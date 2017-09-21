<?php
namespace ORM\Core;

class JoinTable {

    private $tableName;

    private $joinColumnName;
    
    private $inverseJoinColumnName;

    public function getTableName() {
        return $this->tableName;
    }
    
    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }
    
    public function getJoinColumnName() {
        return $this->joinColumnName;
    }
    
    public function setJoinColumnName($joinColumnName) {
        $this->joinColumnName = $joinColumnName;
    }

    public function getInverseJoinColumnName() {
        return $this->inverseJoinColumnName;
    }
    
    public function setInverseJoinColumnName($inverseJoinColumnName) {
        $this->inverseJoinColumnName = $inverseJoinColumnName;
    }
    
}