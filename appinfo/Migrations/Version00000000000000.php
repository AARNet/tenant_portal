<?php

namespace OCA\Tenant_Portal\Migrations;

use \OCP\Migration\ISchemaMigration;
use \Doctrine\DBAL\Schema\Schema;

class Version00000000000000 implements ISchemaMigration {

	/** @var string */
	private $prefix;

	/**
	 - @param Schema $schema
	 - @param [] $options
	 */
	public function changeSchema(Schema $schema, array $options) {
		$this->prefix = $options['tablePrefix'];

		// Initial tp_tenants table
		$tableName = $this->prefix.'tp_tenants';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('code', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['name'], $tableName.'_name_index', ['sorting' => 'ascending']);

		}

		// Initial tp_tenant_config table
		$tableName = $this->prefix.'tp_tenant_config';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('tenant_id', 'integer', [
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('config_key', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('config_value', 'text', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['tenant_id', 'config_key'], $tableName.'_key_tenant_index', ['sorting' => 'ascending']);
		}

		// Initial tp_tenant_users table
		$tableName = $this->prefix.'tp_tenant_users';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('tenant_id', 'integer', [
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 255
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['tenant_id'], $tableName.'_user_index', ['sorting' => 'ascending']);
		}

		// Initial tp_stats table
		$tableName = $this->prefix.'tp_stats';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('timestamp', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP',
				'notnull' => true,
			]);
			$table->addColumn('tenant_id', 'integer', [
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('stat_key', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('stat_value', 'text', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['timestamp', 'tenant_id', 'stat_key']);
			$table->addIndex(['tenant_id'], $tableName.'_user_index', ['sorting' => 'ascending']);
		}

                // Initial tp_collaborators table
                $tableName = $this->prefix.'tp_collaborators';
                if (!$schema->hasTable($tableName)) {
                        $table = $schema->createTable($tableName);
                        $table->addColumn('id', 'integer', [
                                'autoincrement' => true,
                                'notnull' => true,
                                'unsigned' => true,
                                'length' => 11,
                        ]);
                        $table->addColumn('uid', 'string', [
                                'notnull' => true,
				'length' => 64,
                        ]);
                        $table->addColumn('mail', 'string', [
                                'notnull' => true,
				'length' => 255,
                        ]);
                        $table->addColumn('password', 'text', [
                                'notnull' => true,
                        ]);
                        $table->addColumn('salt', 'text', [
                                'notnull' => true,
                        ]);
                        $table->addColumn('cn', 'text', [
                                'notnull' => true,
                        ]);
                        $table->setPrimaryKey(['id']);
                        $table->addIndex(['uid'], $tableName.'_uid_index', ['sorting' => 'ascending']);
                        $table->addIndex(['mail'], $tableName.'_mail_index', ['sorting' => 'ascending']);
                }

                // Initial tp_collaborators_tokens table
                $tableName = $this->prefix.'tp_collaborators_tokens';
                if (!$schema->hasTable($tableName)) {
                        $table = $schema->createTable($tableName);

                        $table->addColumn('collaborator_id', 'integer', [
                                'notnull' => true,
                                'default' => '0',
                                'length' => 11,
                        ]);
                        $table->addColumn('token', 'string', [
                                'notnull' => true,
				'length' => 255,
                        ]);
			$table->addColumn('created', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP',
				'notnull' => true,
			]);
                        $table->setPrimaryKey(['collaborator_id', 'token']);
                        $table->addUniqueIndex(['token'], $tableName.'_token_index', ['sorting' => 'ascending']);
		}

		// Initial tp_project table
		$tableName = $this->prefix.'tp_project';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('user_id', 'integer', [
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('config_key', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('config_value', 'text', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id', 'config_key'], $tableName.'_key_project_index', ['sorting' => 'ascending']);
		}

	}
}	
