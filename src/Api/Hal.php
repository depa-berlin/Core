<?php
namespace Depa\Core\Api;

use Zend\Expressive\Hal\HalResource;
use Zend\Expressive\Hal\Link;

/**
 *
 * @author fenrich
 *        
 */
class Hal
{

    private $selfUri;

    public $hal;

    private $embed;

    private $embedArray = array();

    public function __construct($selfUri = NULL)
    {
        $this->hal = new HalResource();
        if (! is_null($selfUri)) {
            $this->selftUri = $selfUri;
            $this->addLink('self', $selfUri);
        }
    }

    public function addElements(array $dataArray)
    {
        $this->hal = $this->hal->withElements($dataArray);
    }

    public function addElement($name, $value)
    {
        $this->hal = $this->hal->withElement($name, $value);
    }

    public function addLink($name, $uri)
    {
        $this->hal = $this->hal->withLink(new Link($name, $uri));
    }

    public function addEmbed($name, $halResource)
    {
        // Test ob halResource fertig oder ob man noch getHal() machen muss
        $this->embedArray[$name][] = $halResource;
    }

    public function getHal()
    {
        if (count($this->embedArray) > 0) {
            foreach ($this->embedArray as $key => $value) {
                $this->hal = $this->hal->embed($key, $value);
            }
        }
        return $this->hal;
    }
}

