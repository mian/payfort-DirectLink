## Getting Started

Using Direct Link with your PHP project.


### Install via Composer
If you're using [Composer](https://getcomposer.org/doc/00-intro.md#installation-nix) , you can simply run:

```bash
php composer.phar require payfort/DirectLink
```

.. or add a line to your `composer.json` file:

```json
{
    "require": {
        "payfort/DirectLink": "dev-master"
    }
}
```

Now, running `php composer.phar install` will pull the library directly to your local `vendor` folder.



###Example


    $charge= new DirectLink();
    $charge->amount                 = 1000;
    $charge->currency               = 'US';
    $charge->pspId    = 'pspId';
    $charge->pswd            = '12345687';
    $charge->orderID         = '4785ss';
    $charge->userId            = 'userID';
    $charge->win3DS               = 'MAINW';
    $charge->HTTPAccept                = '*/*';
    $charge->HTTPUserAgent      ='Mozilla/4.0';
    $charge->flag3D             = 'N';
    $charge->alias     = '17472cfc3beca1b5f5b3d5e677bba85dsdsdds';
    $charge->eci       =1;
    $type = 'sha1';
    $passPhase= 'ssssss'; //Get it from payfort portal
    $signature= $charge->calculateFortSignature($passPhase,$type);
    $data= $charge->getRequestParams();
    $data['SHASIGN']=$signature;
    $charge->charge(true,$data);
