/**
 * Log activity
 *
 * @param string $action The activity to report
 * @param PropelPDO $con
 * @return <?php echo $className ?>
 */
public function logActivity($action, $con = null)
{
    if ($this->isNew()) {
        throw new PropelException('Unable to log activity on <?php echo $className ?>');
    }

    $activity = new <?php echo $activityTable ?>();
    $activity->set<?php echo $actionColumn ?>($action);
    $activity->set<?php echo $objectColumn ?>('<?php echo $className ?>');
    $activity->set<?php echo $objectPkColumn ?>($this->getPrimaryKey());
    $activity->set<?php echo $createdAtColumn ?>(time());
    $activity->save($con);

    return $this;
}

/**
 * Create a criteria to filter on activity
 */
public function getActivityCriteria()
{
    return <?php echo $activityTable ?>Query::create()
        ->filterBy<?php echo $objectColumn ?>('<?php echo $className ?>')
        ->filterBy<?php echo $objectPkColumn ?>($this->getPrimaryKey());
}

/**
 * Counts activity actions
 *
 * @param string $action The activity to count
 * @param PropelPDO $con
 * @return integer
 */
public function countActivity($action = null, $con = null)
{
    $query = $this->getActivityCriteria();
    if (!is_null($action)) {
        $query->filterBy<?php echo $actionColumn ?>($action);
    }

    return $query->count($con);
}

/**
 * Delete all activities
 *
 * @param string $action
 * @param PropelPDO $con
 * @return PropelCollection
 */
public function flushActivities($action = null, $con = null)
{
    $query = $this->getActivityCriteria();
    if (!is_null($action)) {
        $query->orderBy<?php echo $actionColumn ?>($action);
    }
    
    return $query->delete($con);
}

/**
 * Retrieve last activities
 *
 * @param integer $number
 * @param string $action
 * @param PropelPDO $con
 * @return PropelCollection
 */
public function getLastActivities($number = 10, $action = null, $con = null)
{
    $query = $this->getActivityCriteria()
        ->orderBy<?php echo $createdAtColumn ?>(Criteria::DESC);
    if (!is_null($action)) {
        $query->filterBy<?php echo $actionColumn ?>($action);
    }
    if (intval($number) > 0) {
        $query->limit($number);
    }
    
    return $query->find($con);
}
