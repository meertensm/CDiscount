<?php

namespace MCS;

use DateTime;
use Exception;
use SoapClient;

use PrettyXml\Formatter;

use MCS\CDiscountOrder;
use MCS\CDiscountOrderAddress;
use MCS\CDiscountOrderItem;

class CDiscount
{
    /**
     * URL du token
     * @const string
     */
    const URL = 'https://sts.cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.cdiscount.com/MarketplaceAPIService.svc';

    /**
     * URL du webservice
     * @const string
     */
    const WSDL = 'https://wsvc.cdiscount.com/MarketplaceAPIService.svc?wsdl';

    /**
     * @var SoapClient
     */
    protected $soap;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $passw;

    /**
     * @var stdClass
     */
    protected $lastResult;
    
    protected $zipDir;

    /**
     * @param string $login
     * @param string $passw
     */
    public function __construct($zipDir, $login = null, $passw = null)
    {
        if (!is_dir($zipDir)) {
            throw new Exception("Directory $zipDir does not exist");    
        }
        
        $this->zipDir = rtrim($zipDir, '/') . '/';
        
        $this->setLogin($login)
             ->setPassw($passw);
    }

    /**
     * Conversion des tableaux en objets de maniere recursive
     * @param  array $array
     * @return stdClass
     */
    protected function array2object(array $array)
    {
        return json_decode(json_encode($array));
    }

    /**
     * headerMessage
     * @return stdClass
     */
    protected function getHeaderMessage()
    {
        return $this->array2object(array(
            'Context' => array(
                'CatalogID'      => 1,
                'CustomerPoolID' => 1,
                'SiteID'         => 100,
            ),
            'Localization' => array(
                'Country'         => 'Fr',
                'Currency'        => 'Eur',
                'DecimalPosition' => '2',
                'Language'        => 'En',
            ),
            'Security' => array(
                'DomainRightsList' => null,
                'IssuerID'         => null,
                'SessionID'        => null,
                'SubjectLocality'  => null,
                'TokenId'          => $this->getToken(),
                'UserName'         => null,
            ),
            'Version' => '1.0',
        ));
    }
    
    /**
     * @param  string $login
     * @return CDiscountWsdl
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @param  string $passw
     * @return CDiscountWsdl
     */
    public function setPassw($passw)
    {
        $this->passw = $passw;
        return $this;
    }

    /**
     * @return SoapClient
     */
    public function getSoap()
    {
        if (!$this->soap) {
            $this->soap = new SoapClient(static::WSDL);
        }
        return $this->soap;
    }

    /**
     * @return stdClass
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }
    
    /**
     * @return stdClass
     */
    public function getLastResultArray()
    {
        return json_decode(json_encode($this->lastResult), true);
    }

    /**
     * @return string|bool
     * @throws Exception
     */
    public function getToken()
    {
        if (null !== $this->token) {
            return $this->token;
        }

        $this->token = false;
        $url = parse_url(static::URL);
        $auth = base64_encode($this->login . ':' . $this->passw);
        $fp = @fsockopen('ssl://' . $url['host'], 443, $errno, $errstr, 30);
        if (!$fp) {
            echo "<div class=\"alert alert-danger\">$errstr ($errno)</div>\n";
        } else {
            $header  = "GET {$url['path']}?{$url['query']} HTTP/1.1\r\n";
            $header .= "Host: {$url['host']}\r\n";
            $header .= "Authorization: Basic {$auth}\r\n";
            $header .= "Connection: Close\r\n\r\n";

            $res = '';
            fputs($fp, $header);
            while (!feof($fp)) {
                $res .= fgets($fp, 1024);
            }
            fclose($fp);
            if (preg_match('/<string[^>]+>(.+?)<\/string>/', $res, $array)) {
                $this->token = $array[1];
            } else {
                throw new Exception('The TokenId was not found');
            }
        }
        return $this->token;
    }

    /**
     * Arborescence (07)
     * @return stdClass
     */
    public function getAllowedCategoryTree()
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
        );

        try {
            $this->lastResult = $this->getSoap()->GetAllowedCategoryTree($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }
    
    public function getMethods()
    {
        try {
            $this->lastResult = $this->getSoap()->__getFunctions();
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }
    
    protected function getOrderFilter()
    {   
        $start = new DateTime('-1 week');
        $start = $start->format(DateTime::ATOM);

        $end = new DateTime('tomorrow');
        $end = $end->format(DateTime::ATOM);
        
        return $this->array2object([
            'BeginCreationDate' => $start,    
            'EndCreationDate' => $end, 
            'FetchOrderLines' => true,
            'States' => [
                'CancelledByCustomer',
                'WaitingForSellerAcceptation',
                'AcceptedBySeller',
                'Shipped',
            ]
        ]);
    }
    
    public function getOrders()
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
            'orderFilter' => $this->getOrderFilter()
        );

        try {
            $this->lastResult = $this->getSoap()->GetOrderList($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        $response = $this->getLastResultArray();
        
        $orders = [];
        if (isset($response['GetOrderListResult']['OrderList']['Order'])) {
            $response = $response['GetOrderListResult']['OrderList']['Order'];
            if (is_array($response)) {
                if (isset($response['ArchiveParcelList'])) {
                    $response = [$response];        
                }
                foreach ($response as $order) {
                    $cd_order = new CDiscountOrder(
                        $order,
                        $order['ShippingAddress'],
                        $order['BillingAddress']
                    ); 
                    foreach ($order['OrderLineList']['OrderLine'] as $line) {
                        $cd_order->addOrderItem($line);        
                    }   
                    $orders[$order['OrderNumber']] = $cd_order;
                }
            }
        }
        return $orders;
    }
    
    protected function getOfferFilter()
    {   
        return $this->array2object([]);
    }
    
    public function GetOffers()
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
            'offerFilter' => $this->getOfferFilter()
        );

        try {
            $this->lastResult = $this->getSoap()->GetOfferList($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        $response = $this->getLastResultArray();
        
        $offers = [];
        if (isset($response['GetOfferListResult']['OfferList']['Offer'])) {
            $response = $response['GetOfferListResult']['OfferList']['Offer'];
            if (is_array($response)) {
                foreach ($response as $offer) {
                    $offers[$offer['SellerProductId']] = new CDiscountOffer($offer);
                }
            }
        }
        return $offers;
    }

    public function updateOffers($offers)
    {
        $template = new CDiscountOfferTemplate('update ' . rand(), true);    
        
        if (is_array($offers)) {
            foreach ($offers as $offer) {
                $template->addOffer($offer);        
                $template->addOffer($offer);        
            }
        } else {
            $template->addOffer($offers);    
        }
        
        $xml = $template->get();
        
        $formatter = new Formatter();
        
        $id = rand();
        
        $dir = $this->zipDir;
        
        $id_dir = $dir . $id . '/';
        
        $copy = str_replace('/src' , '/src/copy', __DIR__);
                
        if (file_exists($id_dir)) {
            shell_exec("rm -rf '$id_dir'");
        }
        
        echo shell_exec("cp -r '$copy' '$id_dir'");
        
        file_put_contents($id_dir . 'Content/Offers.xml', $formatter->format($xml));
        
        $zipFile = $dir . $id . '.zip';
        
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
        
        shell_exec("cd $id_dir; zip -r ../$id.zip * -x *.DS_Store*");
        
        if (is_dir($id_dir)) {
            shell_exec("rm -rf '$id_dir'");        
        }
        
        return $zipFile;
    }
    
    /**
     * Soumettre les offres (01)
     * @param  string $zipFileFullPath
     * @return stdClass
     */
    public function submitOfferPackage($zipFileFullPath)
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
            'offerPackageRequest' => $this->array2object(array(
                'ZipFileFullPath' => $zipFileFullPath,
            )),
        );

        try {
            $this->lastResult = $this->getSoap()->SubmitOfferPackage($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }
    
    /**
     * Demander la creation d'un ensemble de produits (08)
     * @param  string $zipFileFullPath
     * @return stdClass
     */
    public function submitProductPackage($zipFileFullPath)
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
            'productPackageRequest' => $this->array2object(array(
                'ZipFileFullPath' => $zipFileFullPath,
            )),
        );

        try {
            $this->lastResult = $this->getSoap()->SubmitProductPackage($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }

    /**
     * Liste des Model (11)
     * @param  string $categoryCode
     * @return stdClass
     */
    public function getModelList($categoryCode)
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
            'modelFilter' => $this->array2object(array(
                'CategoryCodeList' => array(
                    'string' => $categoryCode,
                ),
            )),
        );

        try {
            $this->lastResult = $this->getSoap()->GetModelList($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }

    /**
     * Liste des Model (13)
     * @return stdClass
     */
    public function getAllModelList()
    {
        $this->lastResult = null;
        $params = array(
            'headerMessage' => $this->getHeaderMessage(),
        );

        try {
            $this->lastResult = $this->getSoap()->GetAllModelList($params);
        } catch (SoapFault $exception) {
            echo '<div class="alert alert-danger">' . $exception->getMessage() . '</div>';
        }
        return $this->lastResult;
    }
}
