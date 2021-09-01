<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="ARCEP API Box Tester">
        <meta name="author" content="nPerf.com">
        <title>ARCEP API Box Tester</title>

        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

        <meta name="theme-color" content="#7952b3">
    </head>
    <body>
    <div class="col-md-10 mx-auto p-2 py-md-3">
        <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
              <img src="/img/arcep-share-1200x630.jpg" height="50px">
              <span class="fs-4 ps-3">API Box Tester</span>
            </a>
            <ul class="nav nav-pills">
                <li class="nav-item" id="envCap"><a href="#" class="nav-link<?php echo ($this->Env == "cap"?" active":"") ?>"<?php echo ($this->Env == "cap"?" aria-current=\"page\"":"") ?>>Cap</a></li>
                <li class="nav-item" id="envStaging"><a href="#" class="nav-link<?php echo ($this->Env == "staging"?" active":"") ?>"<?php echo ($this->Env == "staging"?" aria-current=\"page\"":"") ?>>Staging</a></li>
                <li class="nav-item"><a href="#"  id="envProduction" class="nav-link<?php echo ($this->Env == "production"?" active":"") ?>"<?php echo ($this->Env == "production"?" aria-current=\"page\"":"") ?>>Production</a></li>
            </ul>

        </header>
    </div>
    <main data-url-prec="<?php echo ($this->ApiParams?$this->ApiParams->urlPrec:''); ?>" data-url-postc="<?php echo ($this->ApiParams?$this->ApiParams->urlPostc:''); ?>">
        <div class="container col-lg-10 mx-auto">
            <div class="row align-items-end">
                <div class="col-md-6 col-sm-12">
                    <table class="table text-start">
                        <thead>
                        <tr>
                            <th>IP information</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>IP Address (IPv4)</td>
                            <td><code id="ipv4">no</code> <?php echo ($this->IPVersion==4?'<small>(def)</small>':''); ?></td>
                        </tr>
                        <tr>
                            <td>IP Address (IPv6)</td>
                            <td><code id="ipv6" class="text-break">no</code> <?php echo ($this->IPVersion==6?'<small>(def)</small>':''); ?></td>
                        </tr>
                        <tr>
                            <td>IP Reverse</td>
                            <td><code class="text-break"><?php echo (!empty($this->Host)?$this->Host:'-'); ?></code></td>
                        </tr>
                        <tr>
                            <td>AS Number</td>
                            <td><code><?php echo (!empty($this->ASN)?'AS'.$this->ASN:'-'); ?></code></td>
                        </tr>
                        <tr>
                            <td>AS Organization</td>
                            <td><code><?php echo (!empty($this->ASNOrg)?$this->ASNOrg:'-'); ?></code></td>
                        </tr>
                        <tr>
                            <td>Identified ISP for API usage</td>
                            <td><code><?php echo (!empty($this->ApiIspId)?$this->ApiIspId:'-'); ?></code><?php if($this->Env == "cap") echo " (cap env. override)"; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="row">
                        <div class="col-12 bottom-0 mb-3">
                            <?php if(empty($this->ApiIspId)): ?>
                                <div class="alert alert-danger text-center" role="alert">
                                    Your ISP is not supported!
                                </div>
                            <?php elseif(empty($this->ApiParams->urlAccessToken) || empty($this->ApiParams->urlPrec) || empty($this->ApiParams->urlPostc)): ?>
                                <div class="alert alert-warning text-center" role="alert">
                                    API URLs are not set for your ISP.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success text-center" role="alert">
                                    ISP identified, API available.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row pb-3">
                        <div class="col-4">
                            <button type="button" id="BAuth" class="btn btn-primary btn-lg col-12 p-3 disabled">Get token</button>
                        </div>
                        <div class="col-4">
                            <button type="button" id="B1stCall" class="btn btn-primary btn-lg col-12 p-3 disabled">1st Call</button>
                        </div>
                        <div class="col-4">
                            <button type="button" id="B2ndCall" class="btn btn-primary btn-lg col-12 p-3 disabled">2nd Call</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="loader" class="row">
                <div class="col-12 pb-3">
                    <div class="d-grid">
                    <button class="btn btn-light  disabled p-3" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span id="loaderText">Retrieving IP addresses...</span>
                    </button>
                    </div>
                </div>
            </div>
            <div id="error" class="row d-none">
                <div class="col-12">
                    <div class="alert alert-danger text-center" role="alert" id="errorMessage">
                        ERROR
                    </div>
                </div>
            </div>
            <div id="info" class="row d-none">
                <div class="col-12">
                    <div class="alert alert-success text-center" role="alert" id="infoMessage">
                        INFO
                    </div>
                </div>
            </div>
            <div id="howto" class="row mt-md-5 mt-sd-2 mb-md-5 mb-sd-2">
                <div class="col-12">
                    <div class="alert alert-warning" role="alert" id="infoMessage">
                        <h3>Comment utiliser cet outil ?</h3>
                        <p>
                        <ul>
                            <li>L'outil détecte automatiquement l'adresse IP de la connexion et l'opérateur.</li>
                            <li>Si l'opérateur est pris en charge, il est alors possible de lancer la requête qui va récupérer un jeton en utilisant le bouton &quot;<strong>Get token</strong>&quot;.</li>
                            <li>Une fois le jeton récupéré, vous pouvez lancer le premier appel à l'API (<code>prec</code>), en utilisant le bouton &quot;<strong>1st Call</strong>&quot;.</li>
                            <li>Enfin, vous pouvez lancer le second appel à l'API (<code>postc</code>), en utilisant le bouton &quot;<strong>2nd Call</strong>&quot;.</li>
                        </ul>
                        </p>

                    </div>
                </div>
            </div>
            <ul class="nav nav-tabs d-none" id="tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link disabled" id="auth-tab" data-bs-toggle="tab" data-bs-target="#auth" type="button" role="tab" aria-controls="auth" aria-selected="true">Get token</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link disabled" id="call1-tab" data-bs-toggle="tab" data-bs-target="#call1" type="button" role="tab" aria-controls="call1" aria-selected="true">1st Call</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link disabled" id="call2-tab" data-bs-toggle="tab" data-bs-target="#call2" type="button" role="tab" aria-controls="call2" aria-selected="false">2nd Call</button>
                </li>
            </ul>
            <div class="tab-content d-none" id="tabContent">
                <div class="tab-pane fade show active" id="auth" role="tabpanel" aria-labelledby="auth-tab">
                    <div class="row">
                        <div class="col-12">
                            <table class="table text-start">
                                <tr class="bg-light">
                                    <td>Request time (call)</td>
                                    <td class="text-end"><code id="requestTime"></code></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <table class="table text-start">
                                <thead>
                                <tr>
                                    <th>Oauth</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Token Type</td>
                                    <td><code id="tokenType"></code></td>
                                </tr>
                                <tr>
                                    <td>Scope</td>
                                    <td><code class="text-break" id="scope"></code></td>
                                </tr>
                                <tr>
                                    <td>Access Token</td>
                                    <td><code class="text-break" id="accessToken"></code></td>
                                </tr>
                                <tr>
                                    <td>Expires</td>
                                    <td><code id="expires"></code></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="call1" role="tabpanel" aria-labelledby="call1-tab">
                    <?php require __DIR__.'/../Views/PartialTab.php'; ?>
                </div>
                <div class="tab-pane fade" id="call2" role="tabpanel" aria-labelledby="call2-tab">
                    <?php require __DIR__.'/../Views/PartialTab.php'; ?>
                </div>
            </div>


        </div>
    </main>


    <footer class="bg-light text-center text-lg-start">
        <!-- Copyright -->
        <div class="text-center p-3">
            Developed by <a class="text-dark" href="https://www.nperf.com/">nPerf.com</a> for <a class="text-dark" href="https://www.arcep.fr/">ARCEP</a>
        </div>
        <!-- Copyright -->
    </footer>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/js.cookie.min.js"></script>
    <script>

        // Make XHR requests for grabbing IP addresses
        $.ajax({
            url: "<?php echo $_ENV['IPV4_CHECK_URL'] ?>",
            method: "GET",
            dataType: 'json',
        }).fail(function(jqXHR, textStatus) {
            $('main').data('ipv4', {success:false});
            ipCheckEnd();
        }).done(function (data, textStatus) {
            // Store response in DOM
            if(textStatus === "success" && data.error === undefined) {
                data.success = true;
                $('#ipv4').html(data.IPAddress);
            } else {
                data.success = false;
            }
            $('main').data('ipv4', data);

            ipCheckEnd();
        });
        $.ajax({
            url: "<?php echo $_ENV['IPV6_CHECK_URL'] ?>",
            method: "GET",
            dataType: 'json',
        }).fail(function(jqXHR, textStatus) {
            $('main').data('ipv6', {success:false});
            ipCheckEnd();
        }).done(function (data, textStatus) {
            // Store response in DOM
            if(textStatus === "success" && data.error === undefined) {
                data.success = true;
                $('#ipv6').html(data.IPAddress);
            } else {
                data.success = false;
            }
            $('main').data('ipv6', data);

            ipCheckEnd();
        });

        function ipCheckEnd() {
            if($('main').data('ipv4') === undefined) return;
            if($('main').data('ipv6') === undefined) return;

            $('#loader').addClass('d-none');

            if($('main').data('ipv4').success || $('main').data('ipv6').success) {
                // Activate tab
                if($('main').data('urlPrec'))
                    $('#BAuth').removeClass('disabled');
            } else {
                // Display error message
                $('#errorMessage').html("Failed to get public IP addresses");
                $('#error').removeClass('d-none');
            }
        }

        // "Get token" button action
        $('#BAuth').click(function() {
            // UI switches
            $('#B1stCall').addClass('disabled');
            $('#loader').removeClass('d-none');
            $('#error').addClass('d-none');
            $('#info').addClass('d-none');
            $('#loaderText').html('Retrieving access token... [URL: <?php echo ($this->ApiParams?$this->ApiParams->urlAccessToken:'') ?>]');

            // Store chrono start in DOM
            $('main').data('chronoStart', Date.now());

            // Make XHR request
            $.ajax({
                url: "?cmd=getAccessToken",
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({ipv4: $('main').data('ipv4'), ipv6: $('main').data('ipv6')})
            }).fail(function(jqXHR, textStatus) {
                // UI switches
                $('#loader').addClass('d-none');

                // Display appropriate error message
                if(jqXHR.status > 0 || jqXHR.statusText === "timeout") {
                    $('#errorMessage').html("FAILED: "+jqXHR.status+" "+jqXHR.statusText+" error");
                    $('#error').removeClass('d-none');
                } else {
                    $('#errorMessage').html("FAILED: May be caused by CORS misconfiguration on server side. [URL: "+this.url+"]");
                    $('#error').removeClass('d-none');
                }
            }).done(function (data, textStatus) {
                // UI switches
                $('#B1stCall').removeClass('disabled');
                $('#loader').addClass('d-none');

                // Store response in DOM
                $('main').data('access_token', data);

                if(textStatus === "success" && data.error === undefined) {
                    // UI switches
                    $('#infoMessage').html('Get Token: SUCCESS');
                    $('#info').removeClass('d-none');
                    $('#tab').removeClass('d-none');
                    $('#tabContent').removeClass('d-none');
                    $('#howto').addClass('d-none');

                    // Activate tab
                    $('#auth-tab').removeClass('disabled');

                    // Show tab
                    $('#auth-tab').tab('show');

                    // Fill response into UI
                    fillOauthResult(data, $('#auth'));
                } else {
                    // Display error message
                    $('#errorMessage').html($('main').data('access_token').error);
                    $('#error').removeClass('d-none');
                }
            });
        });

        function apiCall(elem, callId) {
            // Disable button
            $(elem).addClass('disabled');

            // Define URL
            var url;
            if(callId === 1) url = $('main').data('urlPrec')
                .replace('{SCOPE}', $('main').data('access_token').scope);
            if(callId === 2) url = $('main').data('urlPostc')
                .replace('{SCOPE}', $('main').data('access_token').scope)
                .replace('{UNIQUE_ID}', $('main').data('call1').id);

            $('#loader').removeClass('d-none');
            $('#loaderText').html('Making API 1st call... [URL: '+url+']');
            $('#error').addClass('d-none');
            $('#info').addClass('d-none');
            $('main').data('chronoStart', Date.now());
            $.ajax({
                url: url,
                method: "GET",
                //dataType: 'json',
                headers: {
                    "Authorization":"Bearer "+$('main').data('access_token').access_token
                }
            }).fail(function(jqXHR, textStatus) {
                // UI switches
                $(elem).removeClass('disabled');
                $('#loader').addClass('d-none');
                console.log(jqXHR);

                // Display appropriate error message
                if(jqXHR.status > 0 || jqXHR.statusText === "timeout") {
                    if(jqXHR.responseJSON !== undefined) {
                        $('#errorMessage').html("FAILED: " + jqXHR.status + " " + jqXHR.statusText + ". ERROR: "+jqXHR.responseJSON.error+" ("+jqXHR.responseJSON.errorDescription+")");
                    } else {
                        $('#errorMessage').html("FAILED: " + jqXHR.status + " " + jqXHR.statusText + " error");
                    }
                    $('#error').removeClass('d-none');
                } else {
                    $('#errorMessage').html("FAILED: May be caused by CORS misconfiguration on server side. [URL: "+this.url+"]");
                    $('#error').removeClass('d-none');
                }
            }).done(function (data, textStatus) {
                $(elem).removeClass('disabled');
                if(callId === 1) $('#B2ndCall').removeClass('disabled');
                $('#loader').addClass('d-none');
                $('main').data('call1', data);
                if(textStatus === "success" && data.error === undefined) {
                    $('#infoMessage').html('1st Call: SUCCESS');
                    $('#info').removeClass('d-none');
                    $('#tab').removeClass('d-none');
                    $('#tabContent').removeClass('d-none');
                    if (callId === 1) {
                        // Activate tab
                        $('#call1-tab').removeClass('disabled');
                        // Show tab
                        $('#call1-tab').tab('show');
                        // Disable tab
                        $('#call2-tab').addClass('disabled');

                        // Fill response into UI
                        fillApiResult(data, $('#call1'));
                    }
                    if (callId === 2) {
                        // Activate tab
                        $('#call2-tab').removeClass('disabled');
                        // Show tab
                        $('#call2-tab').tab('show');

                        // Fill response into UI
                        fillApiResult(data, $('#call2'));
                    }

                } else {
                    $('#errorMessage').html(data.error+' [TOKEN: '+$('main').data('access_token').access_token+']');
                    $('#error').removeClass('d-none');
                }

            });
        }
        $('#B1stCall').click(function () { apiCall(this, 1); });
        $('#B2ndCall').click(function () { apiCall(this, 2); });

        function fillOauthResult(data, node) {
            node.find('#requestTime').html((Date.now() - $('main').data('chronoStart'))+' ms');
            node.find('#tokenType').html(data.token_type);
            node.find('#scope').html(data.scope);
            node.find('#accessToken').html(data.access_token);
            node.find('#expires').html(data.expires);
        }

        function fillApiResult(data, node) {
            node.find('#id').html(data.id);
            node.find('#apiVersion').html(data.apiVersion);
            if(data.timestamp != null) {
                node.find('#timestampApiCallTime').html(data.timestamp.apiCallTime);
                node.find('#timestampLastUpdate').html(data.timestamp.lastUpdate);
            }
            node.find('#requestTime').html((Date.now() - $('main').data('chronoStart'))+' ms');

            node.find('#miscellaneous').html(JSON.stringify(data.miscellaneous));

            if(data.gateway != null) {
                node.find('#gatewayModel').html(data.gateway.model);
                node.find('#gatewaySoftwareVersion').html(data.gateway.softwareVersion);
            }


            if(data.subscriptionSpeed != null) {
                node.find('#subscriptionSpeedDownloadMin').html(data.subscriptionSpeed.downloadMin + ' kb/s');
                node.find('#subscriptionSpeedDownloadMax').html(data.subscriptionSpeed.downloadMax + ' kb/s');
                node.find('#subscriptionSpeedDownloadNormally').html(data.subscriptionSpeed.downloadNormally + ' kb/s');

                node.find('#subscriptionSpeedUploadMin').html(data.subscriptionSpeed.uploadMin + ' kb/s');
                node.find('#subscriptionSpeedUploadMax').html(data.subscriptionSpeed.uploadMax + ' kb/s');
                node.find('#subscriptionSpeedUploadNormally').html(data.subscriptionSpeed.uploadNormally + ' kb/s');
            }

            if(data.wan != null) {
                node.find('#wanTechnology').html(data.wan.technology);
                node.find('#wanMode').html(data.wan.mode);
                node.find('#wanAggregation').html(data.wan.aggregation);
                if (data.wan.speedNT != null) {
                    node.find('#wanSpeedNTDownload').html(data.wan.speedNT.download + ' kb/s');
                    node.find('#wanSpeedNTUpload').html(data.wan.speedNT.upload + ' kb/s');
                    node.find('#wanSpeedNTDownloadEstimated').html(data.wan.speedNT.downloadEstimated + ' kb/s');
                    node.find('#wanSpeedNTUploadEstimated').html(data.wan.speedNT.uploadEstimated + ' kb/s');
                    node.find('#wanSpeedNTDuplex').html(data.wan.speedNT.duplex);
                }
                if (data.wan.speedSynchro != null) {
                    node.find('#wanSpeedSynchroDownload').html(data.wan.speedSynchro.download + ' kb/s');
                    node.find('#wanSpeedSynchroUpload').html(data.wan.speedSynchro.upload + ' kb/s');
                }
                if (data.wan.byteCounter != null) {
                    node.find('#wanByteCounterDownload').html(data.wan.byteCounter.download + ' B');
                    node.find('#wanByteCounterUpload').html(data.wan.byteCounter.upload + ' B');
                }
            }

            if(data.lan != null) {
                node.find('#lanConnectionType').html(data.lan.connectionType);
                node.find('#lanComplexity').html(data.lan.complexity);
                if (data.lan.speedLan != null) {
                    node.find('#lanSpeedLANDownload').html(data.lan.speedLan.download + ' kb/s');
                    node.find('#lanSpeedLANUpload').html(data.lan.speedLan.upload + ' kb/s');
                    node.find('#lanSpeedLANDownloadMax').html(data.lan.speedLan.downloadMax + ' kb/s');
                    node.find('#lanSpeedLANUploadMax').html(data.lan.speedLan.uploadMax + ' kb/s');
                    node.find('#lanSpeedLANDuplex').html(data.lan.speedLan.duplex);
                }
                if (data.lan.wifi != null) {
                    node.find('#lanWifiIeee').html(data.lan.wifi.ieee);
                    node.find('#lanWifiIeeeMax').html(data.lan.wifi.ieeeMax);
                    node.find('#lanWifiRadioBand').html(data.lan.wifi.radioBand);
                    node.find('#lanWifiRssi').html(data.lan.wifi.rssi + ' dBm');
                }
                if (data.lan.byteCounter != null) {
                    node.find('#lanByteCounterDownload').html(data.lan.byteCounter.download + ' B');
                    node.find('#lanByteCounterUpload').html(data.lan.byteCounter.upload + ' B');
                }
            }
        }

        $('#envCap').click(function () {
            if(Cookies.get('env') !== "cap") {
                Cookies.set('env', 'cap');
                location.reload();
            }
        });

        $('#envStaging').click(function () {
            if(Cookies.get('env') !== "staging") {
                Cookies.set('env', 'staging');
                location.reload();
            }
        });

        $('#envProduction').click(function () {
            if(Cookies.get('env') !== "production") {
                Cookies.set('env', 'production');
                location.reload();
            }
        });
    </script>
  </body>

</html>
