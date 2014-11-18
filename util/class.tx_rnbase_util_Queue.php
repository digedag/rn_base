<?php
/**
* Queue class - under PHP 4
*
* @description This is an implementation of FIFO (First In First Out) queue.
*
* @copyright (c) 2003 Michal 'Seth' Golebiowski <sethmail at poczta dot fm>
*   Released under the GNU General Public License
*   license is attached to package in license.txt file
*
* @updated 10.08.2003
*
* @example example.php4 Simple example of puting ang geting datas from queue
*
* @requirement PHP 4
*
*
*
*
* @greetings goes to all developers from Poland especially from php.pl :-)
*/


/**
* Default size of queue
*/
define( 'QUEUE_DEFAULT_SIZE', 15 );


/**
* Implementation of FIFO queue
* @version 1.9
*/
class tx_rnbase_util_Queue {
  var $arrQueue;       // Array of queue items
  var $intBegin;       // Begin of queue - head
  var $intEnd;         // End of queue - tail
  var $intArraySize;   // Size of array
  var $intCurrentSize; // Current size of array


  /**
  * Queue constructor
  * @param int $intQueue - size of queue
  */
  function tx_rnbase_util_Queue( $intSize = QUEUE_DEFAULT_SIZE )
  {
    $this->arrQueue     = Array();
    $this->intArraySize = $intSize;

    $this->clear();
  }
  

  /**
  * Add item to queue
  * @param obj &$objQueueItem - queue item object
  * @return TRUE if added to queue or false if queue is full and item could not be added
  */
  function put( &$objQueueItem  )
  {
    if ( $this->intCurrentSize >= $this->intArraySize )
    {
      return FALSE;
    }

    if ( $this->intEnd == $this->intArraySize - 1 )
    {
      $this->intEnd = 0;
    }
    else
    {
      $this->intEnd++;
    }
    
    $this->arrQueue[$this->intEnd] = $objQueueItem;
    $this->intCurrentSize++;
    
    return TRUE;
  }
  

  /**
  * Get item from queue
  * @return object (queue iteme) or false if there is now items in queue
  */
  function get()
  {
    if ( $this->isEmpty() ){
      return FALSE;
    }
    
    $objItem = $this->arrQueue[$this->intBegin];
    
    if ( $this->intBegin == $this->intArraySize - 1 )
    {
      $this->intBegin = 0;
    }
    else
    {
      $this->intBegin++;
    }
    
    $this->intCurrentSize--;
    
    return $objItem;
  }


  /**
  * Check if queue is empty
  * @return TRUE if it is empty or false if not
  */
  function isEmpty()
  {
    return ( $this->intCurrentSize == 0 ? TRUE : FALSE );
  }


  /**
  * Clear queue
  */
  function clear()
  {
    $this->arrCurrentSize = 0;
    $this->intBegin       = 0;
    $this->intEnd         = $this->intArraySize - 1;
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Queue.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Queue.php']);
}

