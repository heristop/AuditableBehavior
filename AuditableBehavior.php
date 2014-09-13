<?php

/**
 * Track and record changes in your models
 *
 * @author     Alexandre Mogère
 * @license    MIT License
 */

class AuditableBehavior extends Behavior
{
    // default parameters value
    protected $parameters = array(
        'create_label' => 'CREATE',
        'update_label' => 'UPDATE',
        'delete_label' => 'DELETE',
        'audit_create' => true,
        'audit_update' => true,
        'audit_delete' => true,
        'activity_table' => 'audit_activity',
        'activity_action_column' => 'action',
        'object_column' => 'object_class',
        'object_pk_column' => 'object_pk',
        'created_at_column' => 'created_at',
    );
    
    public function modifyDatabase()
    {
        foreach ($this->getDatabase()->getTables() as $table) {
            if ($table->hasBehavior($this->getName())) {
              // don't add the same behavior twice
              continue;
            }
            if (property_exists($table, 'isActivityTable') ||
                $this->getParameter('activity_table') === $table->getName()) {
              // don't add the behavior to activity talbe
              continue;
            }
            $b = clone $this;
            $table->addBehavior($b);
      }
    }
    
    public function modifyTable()
    {
        $table = $this->getTable();
        $database = $table->getDatabase();
        $activityTableName = $this->getParameter('activity_table');
        
        if (!$database->hasTable($activityTableName)) {
            $activityTable = $database->addTable(array(
                'name'      => $activityTableName,
                'package'   => $table->getPackage(),
                'schema'    => $table->getSchema(),
                'namespace' => $table->getNamespace() ? '\\' . $table->getNamespace() : null,
            ));
            
            $activityTable->isActivityTable = true;
            
            // add PK column
            $pk = $activityTable->addColumn(array(
                'name' => 'id',
                'autoIncrement' => 'true',
                'type' => 'INTEGER',
                'primaryKey' => 'true'
            ));
            $pk->setNotNull(true);
            $pk->setPrimaryKey(true);
            
            $activityTable->addColumn(array(
                'name' => $this->getParameter('activity_action_column'),
                'type' => 'VARCHAR',
                'size' => 255,
            ));
            
            $activityTable->addColumn(array(
                'name' => $this->getParameter('object_column'),
                'type' => 'VARCHAR',
                'size' => 255,
            ));
            
            $activityTable->addColumn(array(
                'name' => $this->getParameter('object_pk_column'),
                'type' => 'VARCHAR',
                'size' => 50,
            ));
            
            $activityTable->addColumn(array(
                'name' => $this->getParameter('created_at_column'),
                'type' => 'TIMESTAMP',
            ));
            
            // every behavior adding a table should re-execute database behaviors
            foreach ($database->getBehaviors() as $behavior) {
                $behavior->modifyDatabase();
            }
            
            $this->activityTable = $activityTable;
        }
        else {
            $this->activityTable = $database->getTable($activityTableName);
            $this->activityTable->isActivityTable = true;
        }
    }
    
    public function staticAttributes($builder)
    {
        return $this->renderTemplate('peerStaticAttributes', array(
            'createLabel' => $this->getParameter('create_label'),
            'updateLabel' => $this->getParameter('update_label'),
            'deleteLabel' => $this->getParameter('delete_label')
        ));
    }
    
    public function preInsert($builder)
    {
        if (!$this->getParameter('audit_insert')) {
            return '';
        }
        
        $peerName = $builder->getStubPeerBuilder()->getClassname();
        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());
        
        return $this->renderTemplate('objectPreInsert', array('peerName' => $peerName));
    }
    
    public function preUpdate($builder)
    {
        if (!$this->getParameter('audit_update')) {
            return '';
        }
        
        $peerName = $builder->getStubPeerBuilder()->getClassname();
        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());
        
        return $this->renderTemplate('objectPreUpdate', array('peerName' => $peerName));
    }
    
    public function preDelete($builder)
    {
        if (!$this->getParameter('audit_delete')) {
            return '';
        }
        
        $peerName = $builder->getStubPeerBuilder()->getClassname();
        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());
        
        return $this->renderTemplate('objectPreDelete', array('peerName' => $peerName));
    }
    
    public function objectMethods($builder)
    {
        $className = $builder->getStubObjectBuilder()->getClassname();
        $objectName = strtolower($className);
        $peerName = $builder->getStubPeerBuilder()->getClassname();
        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());
        
        $activityBuilder = $builder->getNewStubObjectBuilder($this->activityTable);
        $builder->declareClassFromBuilder($activityBuilder);
        $activityClass = $activityBuilder->getClassname();
        
        $script = $this->renderTemplate('objectMethods', array(
            'className' => $className,
            'activityTable' => $activityClass,
            'activityActionColumn' => $this->getColumnSetter('activity_action_column'),
            'objectColumn' => $this->getColumnSetter('object_column'),
            'objectPkColumn' => $this->getColumnSetter('object_pk_column'),
            'createdAtColumn' => $this->getColumnSetter('created_at_column'),
        ));
        
        return $script;
    }
    
    protected function getColumnPhpName($name)
    {
        return $this->activityTable->getColumn($this->getParameter($name))->getPhpName();
    }
    
    /**
     * Get the getter of the column of the behavior
     *
     * @param string $name
     *
     * @return string The related getter
     */
    protected function getColumnGetter($name)
    {
        return 'get' . $this->getColumnPhpName($name);
    }
    
    /**
     * Get the setter of the column of the behavior
     *
     * @param string $name
     *
     * @return string The related setter
     */
    protected function getColumnSetter($name)
    {
        return 'set' . $this->getColumnPhpName($name);
    }
    
}
