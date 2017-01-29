<?php 
namespace MCS;
 
use MCS\CDiscountOfferTemplate;

class CDiscountOffer{

    public $BestShippingCharges;
    public $Comments;
    public $CreationDate;
    public $LastUpdateDate;
    public $DeaTax;
    public $EcoTax;
    public $IntegrationPrice;
    public $DiscountList;
    public $IsCDAV;
    public $MinimumPriceForPriceAlignment;
    public $OfferBenchMark;
    public $OfferPoolList;
    public $OfferState;
    public $ParentProductId;
    public $Price;
    public $PriceMustBeAligned;
    public $ProductCondition;
    public $ProductEan;
    public $ProductId;
    public $ProductPackagingUnit;
    public $ProductPackagingUnitPrice;
    public $ProductPackagingValue;
    public $SellerProductId;
    public $Stock;
    public $StrikedPrice;
    public $VatRate;
    public $ShippingInformationList;
    public $PreparationTime = '1';
    
    public function __construct(array $array)
    {
        foreach ($array as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value ? $value : '';
            }
        }
    
        $this->ShippingInformationList = [];
        if (isset($array['ShippingInformationList']['ShippingInformation'])) {
            foreach ($array['ShippingInformationList']['ShippingInformation'] as $info) {
                $this->ShippingInformationList[] = $info;    
            }
        }
    }
    
    public function toArray()
    {
        return json_decode(json_encode($this), true);    
    }
    
    public function toXml()
    {
        $attributes = [
            'SellerProductId',
            'ProductEan',
            'ProductCondition',
            'Price',
            'EcoTax',
            'DeaTax',
            'VatRate',
            'Stock',
            'StrikedPrice',
            'Comment',
            'PreparationTime',
        ];
        
        $array = [
            'ShippingInformationList' => []
        ];
        
        foreach ($attributes as $attribute) {
            
            if ($attribute == 'ProductCondition') {
                $array[$attribute] = 6;
            } else {
                $array[$attribute] = htmlspecialchars($this->{$attribute});            
            }
        }
        
        foreach ($this->ShippingInformationList as $info) {
            $array['ShippingInformationList'][] = [
                'AdditionalShippingCharges' => htmlspecialchars($info['AdditionalShippingCharges']),
                'DeliveryMode' => htmlspecialchars($info['DeliveryMode']['Name']),
                'ShippingCharges' => htmlspecialchars($info['ShippingCharges'])
            ];  
        }
        return CDiscountOfferTemplate::offer($array);
    }
    
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
