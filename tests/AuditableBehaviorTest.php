<?php

class AuditableBehaviorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('AuditableBehaviorTest1')) {
            $schema = <<<EOF
<database name="auditable_behavior_test_applied_on_table">
  <table name="auditable_behavior_test_1">
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
        $this->assertTrue(method_exists('AuditableBehaviorTest1', 'logActivity'));
    }

    public function testAuditActivity()
    {
        $o = new AuditableTable();
        $o->setName('foo');
        $o->save();
        $this->assertEquals(1, $o->countActivity());
        $this->assertEquals(1, $o->countActivity(AuditableBehaviorTest1Peer::AUDIT_LABEL_CREATE));
        $o->setName('bar');
        $o->save();
        $this->assertEquals(2, $o->countActivity());
        $this->assertEquals(1, $o->countActivity(AuditableBehaviorTest1Peer::AUDIT_LABEL_UPDATE));
        $o->logActivity('TEST');
        $this->assertEquals(3, $o->countActivity());
        $this->assertEquals(1, $o->countActivity('TEST'));
        $o->delete();
        $this->assertEquals(4, $o->countActivity());
        $this->assertEquals(1, $o->countActivity(AuditableBehaviorTest1Peer::AUDIT_LABEL_DELETE));
    }
    
}
