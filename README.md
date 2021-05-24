# FIRMA-XAdES-EPES-DIAN
firma digital para facturación electrónica DIAN

<h3>ejemplo de uso</h3>

$firma = new Firma("ruta del cetficado .p12","password del certificado");
$signature = $firma->firmar(xml a firmar);



