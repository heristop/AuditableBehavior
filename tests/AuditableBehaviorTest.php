<?php

class AuditableBehaviorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('MyAuditableTable')) {
            $schema = <<<EOF
<database name="auditable_behavior_test_applied_on_table">
  <table name="my_auditable_table">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="auditable" />
  </table>
</database>
EOF;
        
            PropelQuickBuilder::buildSchema($schema);
        }
    }

    public function testActiveRecordMethods() {
        $this->assertTrue(method_exists('MyAuditableTable', 'logActivity'));
    }

    public function testAuditActivity()
    {
        $o = new MyAuditableTable();
        $o->setName('foo');
        $o->save();
        $this->assertEquals(1, $o->countActivity(), 'CREATE');
        $this->assertEquals(1, $o->countActivity(MyAuditableTablePeer::AUDIT_LABEL_CREATE), 'CREATE');
        $o->setName('bar');
        $o->save();
        $this->assertEquals(2, $o->countActivity(), 'UPDATE');
        $this->assertEquals(1, $o->countActivity(MyAuditableTablePeer::AUDIT_LABEL_UPDATE), 'UPDATE');
        $o->logActivity('TEST');
        $this->assertEquals(3, $o->countActivity(), 'CUSTOM LABEL');
        $this->assertEquals(1, $o->countActivity('TEST'), 'CUSTOM LABEL');
        $o->delete();
        $this->assertEquals(4, $o->countActivity(), 'DELETE');
        $this->assertEquals(1, $o->countActivity(MyAuditableTablePeer::AUDIT_LABEL_DELETE), 'DELETE');
    }
    
    public function testFlushActivities()
    {
        $o = new MyAuditableTable();
        $o->setName('foo');
        $o->save();
        $this->assertEquals(5, $o->countActivity());
        $o->flushActivities();
        $this->assertEquals(0, $o->countActivity());
    }
    
    public function testGetLastActivities()
    {
        $o = new MyAuditableTable();
        $o->setName('foo');
        $o->save();
        for ($i = 0; $i < 20; $i++) {
            $o->logActivity('CONNECTION');
        }
        $this->assertEquals(21, $o->countActivity());
        $this->assertEquals(20, $o->countActivity('CONNECTION'));
        $this->assertEquals(10, count($o->getLastActivities()));
        $this->assertEquals(10, count($o->getLastActivities(10, 'CONNECTION')));
        $this->assertEquals(1, count($o->getLastActivities(10, 'CREATE')));
    }
    
}