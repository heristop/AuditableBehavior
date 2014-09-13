/**
 * Log activity
 *
 * @param String $action The activity to report
 * @param PropelPDO $con
 * @return <?php echo $className ?>
 */
public function logActivity($action, $con = null)
{
    if ($this->isNew()) {
        throw new PropelException('Unable to log activity on <?php echo $className ?>.');
    }

    $activity = new <?php echo $activityTable ?>();
    $activity->set<?php echo $activityActionColumn ?>($action);
    $activity->set<?php echo $objectColumn ?>('<?php echo $className ?>');
    $activity->set<?php echo $objectPkColumn ?>($this->getPrimaryKey());
    $activity->set<?php echo $createdAtColumn ?>(time());
    $activity->save($con);

    return $this;
}

/**
 * Create a criteria to filter on <?php echo $className ?> activity
 */
public function getActivityCriteria()
{
    return <?php echo $activityTable ?>Query::create()
        ->filterBy<?php echo $objectColumn ?>('<?php echo $className ?>')
        ->filterBy<?php echo $objectPkColumn ?>($this->getPrimaryKey());
}

/**
 * Counts activity actions for <?php echo $className ?>
 *
 * @param String $action The activity to count
 * @param PropelPDO $con
 * @return Integer
 */
public function countActivity($action = null, $con = null)
{
    $query = $this->getActivityCriteria();
    if (!is_null($action)) {
        $query-><?php echo $activityActionColumn ?>($action);
    }

    return $query->count($con);
}
