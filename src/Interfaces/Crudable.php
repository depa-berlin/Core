<?php
namespace Depa\Core\Interfaces;

interface Crudable
{

    /**
     * gets a specific row
     *
     * @return mixed
     */
    public function get($data);

    /**
     * creates a new row
     *
     * @return array
     */
    public function create($data);

    /**
     * updates a specific row
     *
     * @return mixed
     */
    public function update($data);

    /**
     * delets a specific row
     *
     * @return mixed
     */
    public function delete($data);

}
