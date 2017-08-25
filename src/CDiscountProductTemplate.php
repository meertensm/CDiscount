<?php 
namespace MCS;
 
use PrettyXml\Formatter;

class CDiscountProductTemplate {
    
    protected $Name;
    protected $products = [];
    
    public function __construct($Name)
    {
        $this->Name = $Name;
    }
    
    public function addProduct($product)
    {
        $this->products[] = $product;            
    }
    
    public function get()
    {
        $template = '<ProductPackage Name="' . htmlspecialchars($this->Name) . '" xmlns="clr-namespace:Cdiscount.Service.ProductIntegration.Pivot;assembly=Cdiscount.Service.ProductIntegration" xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"><ProductPackage.Products><ProductCollection Capacity="' . count($this->products) . '">';
        foreach ($this->products as $product) {
            $template .= self::offer($product);    
        }
        return $template . '</ProductCollection></ProductPackage.Products></ProductPackage>';
    }
    
    public static function offer($array)
    {
        $template = '<Product BrandName="%BrandName%" SellerProductFamily="%SellerProductFamily%" SellerProductColorName="%SellerProductColorName%" Size="%Size%" Description="%Description%" LongLabel="%LongLabel%" Model="%Model%" ProductKind="%ProductKind%" CategoryCode="%CategoryCode%" SellerProductId="%SellerProductId%" ShortLabel="%ShortLabel%" EncodedMarketingDescription="%EncodedMarketingDescription%">';
        $template .= '<Product.ModelProperties></Product.ModelProperties>';
        foreach ($array as $key => $value) {
            if ($key == 'Ean') {
                $template .= '<Product.EanList><ProductEan Ean="' . htmlspecialchars($value) . '"/></Product.EanList>';    
            } else if (!is_array($value)) {
                $template = str_replace("%$key%", htmlspecialchars($value), $template);        
            } else if ($key == 'images') {
                $template .= '<Product.Pictures>';
                foreach ($value as $image) {
                    $template .= '<ProductImage Uri="' . htmlspecialchars($image) . '"/>';    
                }
                $template .= '</Product.Pictures>';
            }
        }
        return $template . '</Product>';
    }
}
