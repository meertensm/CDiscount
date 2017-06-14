<?php 
namespace MCS;
 
use PrettyXml\Formatter;

class CDiscountOfferPriceTemplate {
    
    protected $Name;
    protected $offers = [];
    
    public function __construct($Name)
    {
        $this->Name = $Name;
    }
    
    public function addOffer($offer)
    {
        $this->offers[] = $offer;            
    }
    
    public function get()
    {
        $template = '<OfferPackage Name="' . htmlspecialchars($this->Name) . '" PurgeAndReplace="False" PackageType="StockAndPrice" xmlns="clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration" xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"><OfferPackage.Offers><OfferCollection Capacity="' . count($this->offers) . '">';
        foreach ($this->offers as $offer) {
            $template .= '<Offer SellerProductId="' 
                . htmlspecialchars($offer['SellerProductId']) 
                . '" ProductEan="'
                . htmlspecialchars($offer['ProductEan']) 
                . '" Price="'
                . htmlspecialchars($offer['Price']) 
                . '" Stock="'
                . htmlspecialchars($offer['Stock']) 
                . '"/>';    
        }
        return $template . '</OfferCollection></OfferPackage.Offers></OfferPackage>';
    }
   
}
