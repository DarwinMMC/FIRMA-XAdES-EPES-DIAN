# FIRMA-XAdES-EPES-DIAN
Firma digital para facturación electrónica DIAN

<h3>ejemplo de uso</h3>

 ````$firma = new Firma("ruta del cetficado .p12","password del certificado"); ```` <br>
 ````$signature = $firma->firmar(xml a firmar); ````

<h5>Observación</h5>
Es importante que los NameSpace de la firma coincidan con los NameSpace de la factura

