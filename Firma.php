<?php
class Firma{
    protected $certDigest;
    protected $publicKey;
    protected $privateKey;
    protected $certSerialNumber;
    protected $certIssuer;

    protected $id = [
        "SIGNATURE" => "SIGNATURE-",
        "REFERENCE" => "REFERENCE-",
        "KEYINFO"   => "KEYINFO-",
        "PROPERTIES"=> "PROPERTIES-"
    ];
    protected $xmlns = 'xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';

    const EXC_C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
    const TRANSFORM = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
    const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    const TYPEPROPERTIES = 'http://uri.etsi.org/01903#SignedProperties';
    const IDENTIFIER = 'https://facturaelectronica.dian.gov.co/politicadefirma/v2/politicadefirmav2.pdf';
    const DESCRIPTION = 'Política de firma para facturas electrónicas de la República de Colombia';
    const XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';

    function __construct($PathCert,$PasswordCert){
        $this->get_certificado($PathCert,$PasswordCert);
        $this->setUUID();    
    }
    public function firmar($xml){
        $dom_xml = new DOMDocument('1.0','UTF-8');
        $dom_xml->loadXML($xml);
        $hash_xml = base64_encode(hash("sha256",$dom_xml->C14N(),true));


        //Crear elemento keyinfo
        $KeyInfo = '<ds:KeyInfo Id="'.$this->id['KEYINFO'].'">'.
                      '<ds:X509Data>'.
                        '<ds:X509Certificate>'.$this->publicKey.'</ds:X509Certificate>'.
                      '</ds:X509Data>'.
                    '</ds:KeyInfo>';
       
        //agregar namespace
        $KeyInfo_xmlns = str_replace('<ds:KeyInfo', '<ds:KeyInfo '.$this->xmlns, $KeyInfo);
         //hash keyinfo
        $hash_KeyInfo = base64_encode(hash("sha256",$KeyInfo_xmlns,true));

        //crear elemento signedpropierties
        $SignedProperties = '<xades:SignedProperties Id="'.$this->id['PROPERTIES'].'">'.
        '<xades:SignedSignatureProperties>'.
            '<xades:SigningTime>'.(date('Y-m-d\Th:i:s')."-05:00").'</xades:SigningTime>' .
            '<xades:SigningCertificate>'.
                '<xades:Cert>'.
                    '<xades:CertDigest>'.
                        '<ds:DigestMethod Algorithm="'.self::SHA256.'"></ds:DigestMethod>'.
                        '<ds:DigestValue>'.$this->certDigest.'</ds:DigestValue>'.
                    '</xades:CertDigest>'.
                    '<xades:IssuerSerial>' .
                        '<ds:X509IssuerName>'.$this->certIssuer.'</ds:X509IssuerName>'.
                        '<ds:X509SerialNumber>'.$this->certSerialNumber.'</ds:X509SerialNumber>' .
                    '</xades:IssuerSerial>'.
                '</xades:Cert>'.
            '</xades:SigningCertificate>' .
            '<xades:SignaturePolicyIdentifier>'.
                '<xades:SignaturePolicyId>' .
                    '<xades:SigPolicyId>'.
                        '<xades:Identifier>'.self::IDENTIFIER.'</xades:Identifier>'.
                        '<xades:Description>'.self::DESCRIPTION.'</xades:Description>'.
                    '</xades:SigPolicyId>'.
                    '<xades:SigPolicyHash>' .
                        '<ds:DigestMethod Algorithm="'.self::SHA256.'"></ds:DigestMethod>'.
                        '<ds:DigestValue>dMoMvtcG5aIzgYo0tIsSQeVJBDnUnfSOfBpxXrmor0Y=</ds:DigestValue>'.
                    '</xades:SigPolicyHash>'.
                '</xades:SignaturePolicyId>' .
            '</xades:SignaturePolicyIdentifier>'.
            '<xades:SignerRole>' .
            '<xades:ClaimedRoles>' .
                '<xades:ClaimedRole>supplier</xades:ClaimedRole>' .
            '</xades:ClaimedRoles>' .
            '</xades:SignerRole>' .
        '</xades:SignedSignatureProperties>'.
        '</xades:SignedProperties>';

   
    //agregar namespace
   $SignedProperties_xmlns = str_replace('<xades:SignedProperties', '<xades:SignedProperties '.$this->xmlns, $SignedProperties);
   //hash SignedProperties
   $hash_SignedProperties = base64_encode(hash("sha256",$SignedProperties_xmlns,true));

   //elemento SignedInfo
   $SignedInfo = '<ds:SignedInfo>'.
                    '<ds:CanonicalizationMethod Algorithm="'.self::EXC_C14N.'">'.'</ds:CanonicalizationMethod>'.
                    '<ds:SignatureMethod Algorithm="'.self::RSA_SHA256.'">'.'</ds:SignatureMethod>'.
                    '<ds:Reference Id="'.$this->id['REFERENCE'].'" URI="">'.
                        '<ds:Transforms>'.
                            '<ds:Transform Algorithm="'.self::TRANSFORM.'"></ds:Transform>'.
                        '</ds:Transforms>'.
                        '<ds:DigestMethod Algorithm="'.self::SHA256.'"></ds:DigestMethod>'.
                        '<ds:DigestValue>'.$hash_xml.'</ds:DigestValue>'.
                    '</ds:Reference>'.
                    '<ds:Reference URI="#'.$this->id['KEYINFO'].'">'.
                        '<ds:DigestMethod Algorithm="'.self::SHA256.'"></ds:DigestMethod>'.
                        '<ds:DigestValue>'.$hash_KeyInfo.'</ds:DigestValue>'.
                    '</ds:Reference>'.
                    '<ds:Reference Type="'.self::TYPEPROPERTIES.'" URI="#'.$this->id['PROPERTIES'].'">'.
                        '<ds:DigestMethod Algorithm="'.self::SHA256.'"></ds:DigestMethod>'.
                        '<ds:DigestValue>'.$hash_SignedProperties.'</ds:DigestValue>'.
                    '</ds:Reference>'.
                '</ds:SignedInfo>'; 
    
    //agregar namespace
    $signedinfo_xmlns = str_replace('<ds:SignedInfo', '<ds:SignedInfo '.$this->xmlns, $SignedInfo);
    //firma
    openssl_sign($signedinfo_xmlns, $signatureResult, $this->privateKey,"SHA256");
    $signatureResult = base64_encode($signatureResult);

    $signatue = '<ds:Signature xmlns:ds="'.self::XMLDSIG.'" Id="'.$this->id['SIGNATURE'].'">'.
                    $SignedInfo.
                    '<ds:SignatureValue>'.$signatureResult.'</ds:SignatureValue>'.
                    $KeyInfo.
                    '<ds:Object>'.
                        '<xades:QualifyingProperties Target="#'.$this->id['SIGNATURE'].'">'.
                            $SignedProperties.
                        '</xades:QualifyingProperties>'.
                    '</ds:Object>'.
                '</ds:Signature>';
        return $signatue;
    } 

    public function setUUID(){
        foreach ($this->id as $key => $value) {
            $this->id[$key] = mb_strtoupper("{$value}".sha1(uniqid()));
        }
    }

    public function get_certificado($PathCertP12,$PasswordCert){
        $contenido_certificado = file_get_contents($PathCertP12);
        openssl_pkcs12_read($contenido_certificado, $cert, $PasswordCert);
        $this->privateKey = $cert["pkey"];
        $this->publicKey = $cert["cert"];
        $certData = openssl_x509_parse($this->publicKey);
        $this->certDigest = base64_encode(openssl_x509_fingerprint($this->publicKey, "sha256", true));
        $this->certSerialNumber = $certData['serialNumber'];
        $this->certIssuer = $this->getIssuer($certData['issuer']);
        $this->publicKey = str_replace(["\r", "\n", '-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $this->publicKey);
     
    }
    public function getIssuer($certData){
        $Issuer = array();
        foreach ($certData as $item => $value){
            $Issuer[] = $item.'='.$value;
        }
        $Issuer = implode(', ',array_reverse($Issuer));
        return $Issuer;
    }

}

?>