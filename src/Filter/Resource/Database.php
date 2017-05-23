<?php
namespace Depa\Core\Filter\Resource;

/**
 * Datenbankressource für Core\Filter.
 * Singleton, das eine Datenbankverbindung bei Instanzerstellung erwartet.
 * 
 * @author alex
 *
 */
class Database
{    
	/**
	 * Speichert Filter in DB. Hierfür müssen die ID des übergebenen Objektes gesetzt sein, und natürlich Filter existieren.
	 * 
	 * @param FilterableTrait $filterable
	 */
    public function saveFilters($filterableObject)
    {
        $sqlQuery = "INSERT IGNORE INTO {$filterableObject->getFilterObjectTable()}_filter (`filterable_id`, `category_id`, `filter`) VALUES";
		foreach($filterableObject->getFilter() as $categoryId => $filterArray)
		{
		    foreach ($filterArray as $filterName)
		    {
		        $sqlQuery.="({$filterableObject->getId()}, '{$categoryId}', '{$filterName}'), ";
		    }
		}
		$sqlQuery = substr($sqlQuery, 0, -2);
		$sqlQuery .= ";";
		return $this->db->query($sqlQuery);
    }
    
    /**
     * Lädt die Filter/Filterkategorien für ein Objekt.
     * 
     * @param FilterableTrait $filterable
     * @return array
     */
    public function loadFilters($filterableObject)
    {
        $sqlQuery = "SELECT `category_id`, `filter` FROM
                     {$filterableObject->getFilterObjectTable()}_filter WHERE
                     `filterable_id` = {$filterableObject->getId()}";
        return $this->db->fetchAll($sqlQuery);
    }
    
    /**
     * Lädt alle Filter die die Filterable-Klasse mit dem übergebenen Namen besitzt.
     * 
     * @param string $filterableName
     */
    public function loadAllFilters($filterableName)
    {
        $filterObjectTable = $this->getFilterTableFromFilterable($filterableName);
        $sqlQuery = "SELECT `category_id`, `filter` FROM
                     {$filterObjectTable}_filter";
        return $this->db->fetchAll($sqlQuery);
    }
    
    /**
     * Baut basierend auf den Parametern im übergebenen Query ein SQL-Query zusammen um alle passenden Objekt-IDs zu holen.
     * Die hohe Anzahl von foreach-Loops ist so zu erklären;
     * - Zuerst einmal wird durch jeden Objekt-Typen iteriert
     * - Dann wird durch jede Kategorie des Queries iteriert
     * - Schlussendlich wird jeder Filter der Kategorie eingesetzt.
     * 
     * @param \Core\Filter\Query $query
     * @return multitype:multitype:
     */
    public function loadIdsByQuery(\Core\Filter\Query $query)
    {
        $result = array();
        foreach ($query->getFilterables() as $filterable)
        {
            $filterObjectTable = $filterable['filterObjectTable'];
            $sqlQuery = "SELECT {$filterObjectTable}_filter.filterable_id FROM
                          {$filterObjectTable}_filter WHERE ";
            foreach ($query->getParams() as $categoryId => $filterArray)
            {
                foreach ($filterArray as $filter)
                {
                   $sqlQuery .= "{$filterObjectTable}_filter.category_id = {$categoryId}
                       AND {$filterObjectTable}_filter.filter = {$this->db->quote($filter)} OR ";
                }
                $sqlQuery = substr($sqlQuery, 0, -4);
                if ($query->getPagination() !== null)
                {
                    $sqlQuery .= " LIMIT {$query->getPagination()['page']}, {$query->getPagination()['pageSize']} ORDER BY filterable_id";
                }
                $sqlQuery .= ';';
            }
            $result[$filterable['class']] = array_unique($this->db->fetchCol($sqlQuery));
        }
        return $result;
    }
    
    /**
     * Lädt alle IDs eines Objekt-Typen. Wird benutzt falls im Query keine Parameter gesetzt sind.
     * 
     * @param array $filterableData
     */
    public function loadAllIds($filterableData)
    {
        $sqlQuery = "SELECT {$filterableData['idColumn']} FROM {$filterableData['filterObjectTable']};";
        return $this->db->fetchCol($sqlQuery);
    }
    
    /**
     * Speichert Filter-Kategorie-Daten in die Datenbank
     * 
     * @param array $filterableData
     */
    public function saveFilterCategoryData($data)
    {
        $filterObjectTable = $this->getFilterTableFromFilterable($data['filterable']);
        if ($data['id'] === null)
        {
            $sqlQuery = "INSERT INTO {$filterObjectTable}_filter_category (`name`) VALUES ({$data['name']});";
        }
        else
        {
            $sqlQuery = "UPDATE {$filterObjectTable}_filter_category SET name = '{$data['name']}' WHERE category_id = {$data['id']};";
        }
        return $this->db->query($sqlQuery);
    }
    
    public function loadFilterCategoryData($filterable, $id)
    {
        $filterObjectTable = $this->getFilterTableFromFilterable($filterable);
        $sqlQuery = "SELECT * FROM {$filterObjectTable}_filter_category WHERE category_id = {$id};";
        return $this->db->fetchRow($sqlQuery);
    }
    
    public function getFilterTableFromFilterable($filterable)
    {
        $reflector = new \ReflectionClass($filterable);
        $filterObjectTableMethod = $reflector->getMethod('getFilterObjectTable');
        return $filterObjectTableMethod->invoke($reflector->newInstanceWithoutConstructor());
    }
}