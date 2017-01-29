<?php 
namespace MCS;
 
use DateTime;

use MCS\CDiscountOrderItem;
use MCS\CDiscountOrderAddress;

class CDiscountOrder{

    public $OrderNumber;
    public $PartnerOrderRef;
    public $OrderState;
    public $Status;
    public $VisaCegid;
    public $ValidationStatus;
    public $HasClaims;
    public $Email;
    public $Phone;
    public $MobilePhone;
    
    public $ModGesLog;
    public $Offer;
    public $OrderLineList;
    public $InitialTotalAmount;
    public $InitialTotalShippingChargesAmount;
    public $ShippedTotalAmount;
    public $ShippedTotalShippingCharges;
    public $SiteCommissionPromisedAmount;
    public $SiteCommissionShippedAmount;
    public $ValidatedTotalAmount;
    public $ValidatedTotalShippingCharges;
    public $ShippingCode;
    
    public $IsCLogistiqueOrder;
    
    public $LastUpdatedDate;
    public $ModifiedDate;
    public $ShippingDateMax;
    public $ShippingDateMin;
    
    public $ShippingAddress;
    public $BillingAddress;
    public $OrderItems = [];
       
    
    /**
     * Construct
     * @param string $id The orderId
     * @param array $ShippingAddress 
     * @param array $BillingAddress  
     * @param object BolPlazaClient $client 
     */
    public function __construct(array $array, array $ShippingAddress, array $BillingAddress)
    {
        foreach ($array as $property => $value) {
            if (!is_array($value)) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
        
        $this->ShippingAddress = new CDiscountOrderAddress($ShippingAddress);
        $this->BillingAddress = new CDiscountOrderAddress($BillingAddress);
    }
    
    /**
     * Add an item to the order
     * @param array $item
     */
    public function addOrderItem(array $item)
    {
        $this->OrderItems[] = new CDiscountOrderItem($item);
    }
}
