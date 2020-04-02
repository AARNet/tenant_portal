<?php
namespace OCA\Tenant_Portal\Managers;

use \OCA\Tenant_Portal\Util;

class NotCreatableException extends \Exception {}
class NotFoundException extends \Exception {}

class ProjectFolderManager {
	const DEFAULT_SHARE_PERMISSIONS = 31;

	private $userId;
	private $userFolder;
	private $userManager;
	private $userSession;
	private $shareManager;

	const ITEMTYPE_FOLDER = 'folder';

	/**
	 * @param string $user_id
	 */
	public function __construct($user_id) {
		$this->userId = $user_id;
		$this->userFolder = \OC::$server->getUserFolder($user_id);
		$this->userManager = \OC::$server->getUserManager();
		$this->userSession = \OC::$server->getUserSession();
		if (\OCP\Util::getVersion()[0] >= 10) {
			$this->shareManager = \OC::$server->getShareManager();
		} else {
			$this->shareManager = null;
		}
	}

	/**
	 * Create new folder
	 * @param string $path
	 * @return Node
	 */
	public function create($path) {
		if (!$this->userFolder->isCreatable()) {
			throw new NotCreatableException();
		}
		return $this->userFolder->newFolder($path);
	}

	/**
	 * Get a node
	 * @param string $path
	 * @return Node
	 */
	public function get($path) {
		return $this->userFolder->get($path);
	}

	/**
	 * Check if a node is a folder
	 * @param string $path
	 * @return boolean
	 */
	public function isFolder($path) {
		$folder = $this->userFolder->get($path);
		if (!$this->nodeExists($path)) {
			return false;
		}
		return ($folder->getType() === \OCP\Files\FileInfo::TYPE_FOLDER);
	}

	/**
	 * Check if a node exists
	 * @param string $path
	 * @return boolean
	 */
	public function nodeExists($path) {
		return $this->userFolder->nodeExists($path);
	}

	/**
	 * Share a folder to the user
	 * @param string $path
	 * @param string $user
	 * @param integer $permissions
	 */
	public function shareToUser($path, $projectOwner, $user, $permissions=null) {
		if (!$this->isFolder($path)) {
			throw new NotFoundException();
		}
		$currentUid = Util::currentUser();
		$currentUser = $this->userManager->get($currentUid);
		if ($projectOwner = Util::getRealUID($projectOwner)) {
			try {
				$user = Util::getRealUID($user);
				$projectOwnerUser = $this->userManager->get($projectOwner);
				$permissions = (is_null($permissions) ? self::DEFAULT_SHARE_PERMISSIONS : $permissions);

				// Switch user, tear down current file system and mount the project owner's filesystem
				$this->setupUserFilesystem($projectOwner);

				// Perform share
				if (!is_null($this->shareManager)) { // Ownlcoud 10
					$doShare = $this->shareManager->newShare();
					$doShare->setNode($this->get($path))
						->setSharedBy($projectOwnerUser->getUid())
						->setSharedWith($user)
						->setShareType(\OCP\Share::SHARE_TYPE_USER)
						->setPermissions($permissions);
					$doShare = $this->shareManager->createShare($doShare);
				} else { // Owncloud 8.2
					$itemSource = $this->get($path)->getId();
					$itemSourceName = $path;
					$expirationDate = null; // No expiration date as we're not doing share links
					$passwordChanged = null; // No password change value, unnecessary
					$doShare = \OCP\Share::shareItem(self::ITEMTYPE_FOLDER, $itemSource, \OCP\Share::SHARE_TYPE_USER, $user, $permissions, $itemSourceName, $expirationDate, $passwordChanged);
				}

				// Switch back to the actual user and re-mount the users filesystem
				$this->setupUserFilesystem($currentUid);
				Util::debugLog("@ProjectFolder#Share [user=>{$currentUid}, project=>$projectOwner, shared_to=>$user]");

				return $doShare;
			} catch (\Exception $e) {
				$this->setupUserFilesystem($currentUid);
				throw $e;
			}
		}
	}

	/**
	 * Setup another user's filesystem
	 * @param string $uid
	 */
	public function setupUserFilesystem($uid) {
		$user = $this->userManager->get($uid);
		\OC::$server->getUserSession()->setUser($user);
		\OC_Util::tearDownFS();
		\OC_Util::setupFS($user->getUID());
	}

	/**
	 * Change the permissions for a user on the folder
	 * @param string $path
	 * @param string $user
	 * @param integer $permissions
	*/
	public function setSharePermissions($path, $user, $permissions=null) {
		if (!$this->isFolder($path)) {
			throw new NotFoundException();
		}
		$itemSource = $this->get($path)->getId();
		$itemSourceName = $itemSource;
		$permissions = (is_null($permissions) ? self::DEFAULT_SHARE_PERMISSIONS : $permissions);
		\OCP\Share::setPermissions(self::ITEMTYPE_FOLDER, $itemSource, \OCP\Share::SHARE_TYPE_USER, $user, $permissions);
	}

	/**
	 * Unshare a folder
	 * @param string $path
	 * @param string $user
	 */
	public function unshareToUser($path, $user) {
		if (!$this->isFolder($path)) {
			throw new NotFoundException();
		}
		$currentUser = Util::currentUser();
		$user = Util::getRealUID($user);
		$itemSource = $this->get($path)->getId();
		\OCP\Share::unshare(self::ITEMTYPE_FOLDER, $itemSource, \OCP\Share::SHARE_TYPE_USER, $user, $this->userId);
		Util::debugLog("@ProjectFolder#Unshare [user=>{$currentUser}, shared_to=>$user]");

	}

	/**
	 * Unshare a folder from all users
	 * @param string $path
	 */
	public function unshareToAll($path) {
		if (!$this->isFolder($path)) {
			throw new NotFoundException();
		}
		$currentUser = Util::currentUser();
		$itemSource = $this->get($path)->getId();
		\OCP\Share::unshareAll(self::ITEMTYPE_FOLDER, $itemSource);
		Util::debugLog("@ProjectFolder#UnshareAll [user=>$currentUser, project=>$projectOwner]");
	}

	/**
	 * Retrieve users on a share
	 * @param string $path
	 * @return Array
	 */
	public function getShareUsers($path) {
		if (!$this->isFolder($path)) {
			throw new NotFoundException();
		}
		$itemSource = $this->get($path)->getId();
		$sharedUsers = \OCP\Share::getUsersItemShared(self::ITEMTYPE_FOLDER, $itemSource, $this->userId);
		$result = Array();
		// Get details on share for each user
		foreach ($sharedUsers as $user) {
			$item = \OCP\Share::getItemSharedWithUser(self::ITEMTYPE_FOLDER, $itemSource, $user, $this->userId);
			if (!empty($item)) {
				$result = array_merge($result, $item);
			}
		}
		return $result;
	}
}
