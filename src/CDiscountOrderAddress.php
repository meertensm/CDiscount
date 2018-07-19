<?php 
namespace MCS;
 
class CDiscountOrderAddress{

    public $FirstName;
    public $LastName;
    public $Street;
    public $Address1;
    public $Address2;
    public $Building;
    public $ApartmentNumber;
    public $ZipCode;
    public $City;
    public $Country;
    public $ApartmentNumber;
    public $Building;
    public $Civility;
    public $CompanyName;
    public $County;
    public $Instructions;
    public $PlaceName;
    
    public function __construct(array $array)
    {
        foreach ($array as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value ? $value : '';
            }
        }
    }
    
    /**
     * Check if the address has a property
     * @param  boolean
     */
    public function has($property)
    {
        if (property_exists($this, $property)) {
            return is_null($this->{$property}) ? false : true;    
        } else {
            return false;    
        }
    }
    
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            return '';    
        }
    }
}
