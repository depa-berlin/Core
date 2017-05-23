<?php

namespace Depa\Core\Filter;

interface FilterableInterface
{
    public function getId();
    
    public function isNull();
    
    public function getFilterObjectTable();
    
    public function getIdColumn();
    
    public function inFilterCategory(Category $category);
    
    public function getFilterValue(Category $category);
    
    public function setFilterValue(Category $category, $value);
    
    public function addFilterValue(Category $category, $value);
    
    public function removeFilterValue(Category $category, $value);
    
    public function hasFilterValueForCategory(Category $category, $value);
    
    public function getFilters();
    
    public function loadFilterData($databaseConnection);
    
    public function saveFilterData($databaseConnection);
}