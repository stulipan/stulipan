<?php

namespace App\Controller\Shop;

//use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QrCodeController extends AbstractController
{

    public function generateForeignKeyConstraint($columnNames, $prefix='', $maxSize=30)
    {
        $hash = implode("", array_map(function($column) {
            return dechex(crc32($column));
        }, $columnNames));

        return substr(strtoupper($prefix . "_" . $hash), 0, $maxSize);
    }

    /**
     * @Route("/show-string", name="show-string")
     */
    public function showStrings()
    {
        $columnNames = [
            "cart_order_history", // table name
            "channel_id",
            "id"
        ];
        $string = $this->generateForeignKeyConstraint($columnNames, 'IDX', 19);
        return new Response($string);
    }

    /**
     * @Route("/qrcode", name="qrcode")
     */
    public function generateQrCode(BuilderInterface $qrCodeBuilder, string $exportDirectory)
    {
        $urls = [
            "PCY6537"	 =>	"https://www.granjoyeria.es/products/pcy6537?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "P607"	 =>	"https://www.granjoyeria.es/products/p607?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36695"	 =>	"https://www.granjoyeria.es/products/y36695?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36608"	 =>	"https://www.granjoyeria.es/products/y36608?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36474"	 =>	"https://www.granjoyeria.es/products/y36474?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36415"	 =>	"https://www.granjoyeria.es/products/y36415?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36447"	 =>	"https://www.granjoyeria.es/products/y36447?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36550"	 =>	"https://www.granjoyeria.es/products/y36550?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36487"	 =>	"https://www.granjoyeria.es/products/y36487?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36689"	 =>	"https://www.granjoyeria.es/products/y36689?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36611"	 =>	"https://www.granjoyeria.es/products/y36611?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36711"	 =>	"https://www.granjoyeria.es/products/y36711?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36490"	 =>	"https://www.granjoyeria.es/products/y36490?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36364"	 =>	"https://www.granjoyeria.es/products/y36364?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36533"	 =>	"https://www.granjoyeria.es/products/y36533?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36521"	 =>	"https://www.granjoyeria.es/products/y36521?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36502"	 =>	"https://www.granjoyeria.es/products/y36502?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36393"	 =>	"https://www.granjoyeria.es/products/y36393?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36685"	 =>	"https://www.granjoyeria.es/products/y36685?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36529"	 =>	"https://www.granjoyeria.es/products/y36529?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36380"	 =>	"https://www.granjoyeria.es/products/y36380?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36467"	 =>	"https://www.granjoyeria.es/products/y36467?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36660"	 =>	"https://www.granjoyeria.es/products/y36660?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36699"	 =>	"https://www.granjoyeria.es/products/y36699?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y35453"	 =>	"https://www.granjoyeria.es/products/y35453?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "Y36807"	 =>	"https://www.granjoyeria.es/products/y36807?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY4658"	 =>	"https://www.granjoyeria.es/products/joya-5270?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7095"	 =>	"https://www.granjoyeria.es/products/pcy7095?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY3962"	 =>	"https://www.granjoyeria.es/products/joya-1777?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7094"	 =>	"https://www.granjoyeria.es/products/pcy7094?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "NRI0022"	 =>	"https://www.granjoyeria.es/products/nri0022?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "NRI0020"	 =>	"https://www.granjoyeria.es/products/nri0020?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY1734"	 =>	"https://www.granjoyeria.es/products/pcy1734?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7285"	 =>	"https://www.granjoyeria.es/products/pcy7285?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7360"	 =>	"https://www.granjoyeria.es/products/pcy7360?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7389"	 =>	"https://www.granjoyeria.es/products/pcy7389?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
            "PCY7291"	 =>	"https://www.granjoyeria.es/products/pcy7291?utm_source=tv_show&utm_medium=qrcode&utm_campaign=nova_tv_2022_04_10",
        ];
        $response = '';

        foreach ($urls as $sku => $url) {
            $qrCode = $qrCodeBuilder
                ->data($url)
                ->size(1024)
                ->margin(20)
//                ->size(192)
//                ->margin(4)
                ->build();

            // Directly output the QR code
            header('Content-Type: '.$qrCode->getMimeType());
//            echo $qrCode->getString();

//            dd($exportDirectory.'/'.$sku.'.png');
            // Save it to a file
            $qrCode->saveToFile($exportDirectory.'/'.$sku.'.png');
            $response .= $sku . '   =>  ' . $url . "<br>";
        }
        return new Response($response);
    }
}