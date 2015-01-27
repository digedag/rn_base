rn_base
=======

[![Latest Stable Version](https://poser.pugx.org/digedag/rn-base/v/stable.svg)](https://packagist.org/packages/digedag/rn-base)
[![License](https://poser.pugx.org/digedag/rn-base/license.svg)](https://packagist.org/packages/digedag/rn-base)  
[CHANGELOG](CHANGELOG.md)

What is this extension for?
---------------------------

This library is based and includes many code of extension "lib". I wrote this extension because I don't like the code design of "lib". For my taste there is too much inheritance, too much dependency and unclear responsibilities between the used classes.


[So what is changed?](CHANGELOG.md)

Since I really like the base ideas of extension lib I took it and stripped it down. So the first player is class "controller". This is mainly the new plugin class that is the entrypoint of TYPO3. This class has no parent.

Since the controller-class should not be responsible for your business-logic this task is given to "Action-Classes". This is different to original "lib", that used "Action-Methods" instead. But this would lead to huge classes containing code for different task. But in OO-Programming small classes are preferred.

This library does not make any expectations about your business model. So you can use anything you want. But notice that class tx_rnbase_util_DB has a nice feature: When you retrieve data from database you would normally get an result array (rows) of arrays (columns). But with tx_rnbase_util_DB you can provide a wrapper class for your database requests. And so for each result row, one instance of your wrapper is created and the result row is given as parameter.

