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
    <body style="height: 100vh;">
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
    <main>
        <div class="container h-100">
            <div class="row align-items-center h-100">
                <div class="col-12 mx-auto">
                    <div class="alert alert-danger text-center" role="alert" id="errorMessage">
                        <?php echo $this->Error; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>

    <script>
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
