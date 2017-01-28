<?php 
namespace MCS;
 
use PrettyXml\Formatter;

class CDiscountOfferTemplate {
    
    protected $PurgeAndReplace = 'False';
    protected $Name;
    protected $offers = [];
    
    public function __construct($Name, $PurgeAndReplace)
    {
        $this->Name = $Name;
        $this->PurgeAndReplace = $PurgeAndReplace == false ? 'False' : 'True';  
    }
    
    public function addOffer($offer)
    {
        $this->offers[] = $offer;            
    }
    
    public function get()
    {
        $template = '<OfferPackage Name="' . htmlspecialchars($this->Name) . '" PurgeAndReplace="' . $this->PurgeAndReplace . '" xmlns="clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration" xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"><OfferPackage.Offers><OfferCollection Capacity="' . count($this->offers) . '">';
        foreach ($this->offers as $offer) {
            $template .= $offer->toXml();    
        }
        return $template . '</OfferCollection></OfferPackage.Offers></OfferPackage>';
    }
    
    public static function offer($array)
    {
        $formatter = new Formatter();
        $template = '<Offer SellerProductId="%SellerProductId%" ProductEan="%ProductEan%" ProductCondition="%ProductCondition%" Price="%Price%" EcoPart="%EcoPart%" DeaTax="%DeaTax%" Vat="%Vat%" Stock="%Stock%" StrikedPrice="%StrikedPrice%" Comment="%Comment%" PreparationTime="%PreparationTime%"></Offer>';
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $template = str_replace("%$key%", htmlspecialchars($value), $template);        
            } else if ($key == 'ShippingInformationList') {
                $list = '<Offer.ShippingInformationList><ShippingInformationList Capacity="' . count($key) . '">';        
                foreach ($value as $ShippingInformationList) {
                    $temp = '<ShippingInformation AdditionalShippingCharges="%AdditionalShippingCharges%" DeliveryMode="%DeliveryMode%" ShippingCharges="%ShippingCharges%" />';
                    foreach ($ShippingInformationList as $k => $v) {
                        $temp = str_replace("%$k%", htmlspecialchars($v), $temp);        
                    }
                    $list .= $temp;
                }
                $template = str_replace('</Offer>', $list . '' . '</ShippingInformationList></Offer.ShippingInformationList></Offer>', $template);
            }
        }
        return $template;
    }
}
