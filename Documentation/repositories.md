#Repositories

## Search Repository

There is a abstract repository `Sys25\RnBase\Domain\Repository\AbstractRepository` whose only job is to help you fetch entities of a certain type.

## Persistence Repository

The persistence repository `Sys25\RnBase\Domain\Repository\PersistenceRepository` 
extends the search repository and provides manipulation methods.

There are two new methods in the repository:

* `Sys25\RnBase\Domain\Model\DomainInterface createNewModel ( [ array $record = array() ] )` :  
   Creates a new model instance, optionaly with a initial record
* `void persist ( Sys25\RnBase\Domain\Model\DomainInterface $model [, array $options = array() ] )` :  
   persists a model in the database.  
   For a new models a insert will be performed.  
   For exiting entries a update will be performed.

little example:
``` php
// instanciate the repo
$repo = new PersistenceRepository();
// create new Model
$model = $repo->createNewModel();
$model->setTitle('Hello');
// write new entry to db
$repo->persist($model);
// change data
$model->setTitle('Hello World');
// update the db entry
$repo->persist($model);
// change data
$model->setDeleted(1);
// delete the entry
$repo->persist($model);
```