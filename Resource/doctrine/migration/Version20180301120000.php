<?php
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Doctrine\ORM\EntityManager;

/**
 * Class Version20180301120000.
 */
class Version20180301120000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const TABLE = 'plg_banner';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Plugin\Banner\Entity\Banner',
    );

    protected $sequence = array(
        'plg_banner_banner_id_seq',
    );

    /**
     * Up method
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->createTable($schema);
    }

    /**
     * Down method
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $app = Application::getInstance();
        $meta = $this->getMetadata($app['orm.em']);
        $tool = new SchemaTool($app['orm.em']);
        $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);
        // delete table
        foreach ($schemaFromMetadata->getTables() as $table) {
            if ($schema->hasTable($table->getName())) {
                $schema->dropTable($table->getName());
            }
        }
        // delete seq
        foreach ($schemaFromMetadata->getSequences() as $sequence) {
            if ($schema->hasSequence($sequence->getName())) {
                $schema->dropSequence($sequence->getName());
            }
        }

        if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            foreach ($this->sequence as $sequence) {
                if ($schema->hasSequence($sequence)) {
                    $schema->dropSequence($sequence);
                }
            }
        }
    }

    /**
     * Create maker table.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createTable(Schema $schema)
    {
        if ($schema->hasTable(self::TABLE)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata($this->entities[0]),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * Get metadata.
     *
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }
}
