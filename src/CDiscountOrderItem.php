<?php 
namespace MCS;
 
use DateTime;

class CDiscountOrderItem{
    
    public $Quantity;
    public $Name;
    public $ProductId;
    public $SellerProductId;
    public $ProductEan;
    public $Sku;
    public $SkuParent;
    public $UnitAdditionalShippingCharges;
    public $UnitShippingCharges;
    public $RowId;
    public $PurchasePrice;
    public $ProductCondition;
    public $AcceptationState;
    public $CategoryCode;
    public $DeliveryDateMax;
    public $DeliveryDateMin;
    public $ShippingDateMax;
    public $ShippingDateMin;
    public $HasClaim;
    public $InitialPrice;
    public $IsCDAV;
    public $IsNegotiated;
    public $IsProductEanGenerated;
    
    public function __construct(array $array)
    {
        foreach ($array as $property => $value) {
            if (property_exists($this, $property)) {
                if (is_array($value)) {
                    $value = '';
                }
                $this->{$property} = $value;
            }
        }
    }
    
    public function has($property)
    {
        if (property_exists($this, $property)) {
            return is_null($this->{$property}) ? false : true;    
        } else {
            return false;    
        }
    }
}
