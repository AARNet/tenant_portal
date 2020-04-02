<?php
namespace OCA\tenant_portal\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OCP\Migration\ISchemaMigration;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version20180319035541 implements ISchemaMigration {
	private $prefix;

	public function changeSchema(Schema $schema, array $options) {
		$this->prefix = $options['tablePrefix'];
                $tableName = $this->prefix.'tp_audit_log';
                if (!$schema->hasTable($tableName)) {
                        $table = $schema->createTable($tableName);
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
			$table->addColumn('timestamp', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP',
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'text', [
                                'notnull' => true,
			]);
			$table->addColumn('tenant_id', 'integer', [
				'notnull' => true,
				'unsigned' => true,
				'length' => 11,
			]);
                        $table->addColumn('action', 'text', [
                                'notnull' => true,
                        ]);
                        $table->addColumn('details', 'text', [
                                'notnull' => false,
                        ]);
                        $table->setPrimaryKey(['id']);
                        $table->addIndex(['tenant_id'], $tableName.'_tenant_id_index', ['sorting' => 'ascending']);
		}
	}
}
