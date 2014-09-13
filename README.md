[![Build Status](https://travis-ci.org/heristop/AuditableBehavior.svg)](https://travis-ci.org/heristop/AuditableBehavior)

AuditableBehavior
====================

Installation
------------

Download the AuditableBehavior.php file in src/, put it somewhere on your project, then add the following line to your propel.ini:

``` ini
propel.behavior.auditable.class = path.to.AuditableBehavior
```

Or use composer adding the requirement below:

``` js
{
    "require": {
        "heristop/propel-auditable-behavior": "*"
    }
}
```

Usage
-----

Add this line to your schema.xml:

``` xml
<behavior name="auditable" />
```

The Behavior will add several methods to the object:

``` php
public function countActivity()
public function flushActivities()
public function getLastActivities()
```
