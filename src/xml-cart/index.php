<?php
declare(strict_types=1);

const XML_FILE_PATH = 'cart.xml';
const XPATH_SEARCH_QUERY_TPL = "/cart/item[sku='%s']";
const COMMAND_MAPPING = [
    'add' => 'addToCart',
    'remove' => 'removeFromCart'
];

function getXmlDataObject(string $filePath): SimpleXMLElement
{
    if (!file_exists(XML_FILE_PATH)) {
        createXmlFile($filePath);
    }

    $xmlDataObject = simplexml_load_file($filePath);
    if ($xmlDataObject === false) {
        throw new Exception("Error occured while loading data from xml file." . $filePath);
    }

    return $xmlDataObject;
}

function createXmlFile(string $filePath): void
{
    $sampleXmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<cart>
    <item>
        <sku>1111</sku>
        <qty>3</qty>
    </item>
    <item>
        <sku>2222</sku>
        <qty>1</qty>
    </item>
    <item>
        <sku>3333</sku>
        <qty>44</qty>
    </item>
</cart>
XML;

    putContentToXmlFile($sampleXmlData, $filePath);
}

function putContentToXmlFile(string $content, string $filePath): void
{
    $result = file_put_contents($filePath, $content);

    if ($result === false) {
        throw new Exception("Error occured while saving data to file.");
    }
}

function findItemBySku(string $sku, SimpleXMLElement $xmlCart): array
{
    $searchQuery = sprintf(XPATH_SEARCH_QUERY_TPL, $sku);
    return $xmlCart->xpath($searchQuery);
}

function addToCart(string $sku, string $qty, SimpleXMLElement $xmlCart): void
{
    $outPutMessage = sprintf('Qty of item with sku: %s was increased.', $sku);

    if ((int)$qty <= 0) {
        throw new Exception("qty param must be non-zero positive number!");
    }

    $foundItem = findItemBySku($sku, $xmlCart);
    if (!$foundItem || !$foundItem[0]) {
        $newItem = $xmlCart->addChild('item');
        $newItem->addChild('sku', $sku);
        $newItem->addChild('qty', $qty);
        echo sprintf('Item with sku: %s was added to cart.', $sku), PHP_EOL;
        return;
    }

    $foundItem[0]->qty = (int)$foundItem[0]->qty + (int)$qty;
    echo $outPutMessage, PHP_EOL;
}

function removeFromCart(string $sku, string $qty, SimpleXMLElement $xmlCart): void
{
    $outPutMessage = sprintf('Qty of Item with sku: %s was decreased.', $sku);

    if ((int)$qty <= 0) {
        throw new Exception("qty param must be non-zero positive number!");
    }

    $foundItem = findItemBySku($sku, $xmlCart);
    if (!$foundItem || !$foundItem[0]) {
        echo sprintf('Item with sku: %s not found', $sku), PHP_EOL;
        return;
    }

    $qtyDifference = (int)$foundItem[0]->qty - (int)$qty;
    if ($qtyDifference <= 0) {
        unset($foundItem[0][0]);
        echo sprintf('Item with sku: %s was removed from cart.', $sku), PHP_EOL;
        return;
    }

    $foundItem[0]->qty = $qtyDifference;
    echo $outPutMessage, PHP_EOL;
}

function formatXml(SimpleXMLElement $simpleXMLElement)
{
    $xmlDocument = new DOMDocument('1.0');
    $xmlDocument->preserveWhiteSpace = false;
    $xmlDocument->formatOutput = true;
    $xmlDocument->loadXML($simpleXMLElement->asXML());

    return $xmlDocument->saveXML();
}

function validateValues(string $sku, string $qty, array &$errorMessages): bool
{
    $isValid = true;
    if (!preg_match('/^\w+$/', $sku)) {
        $errorMessages[] = 'SKU value must be in range of symbols [A-Za-z0-9_]' . PHP_EOL;
        $isValid = false;
    }

    if (!preg_match('/^\d+$/', $qty) || (int)$qty <= 0) {
        $errorMessages[] = 'QTY value must be a positive number' . PHP_EOL;
        $isValid = false;
    }

    return $isValid;
}

function processRequest(string $command, string $sku, string $qty): void
{
    $xmlCart = getXmlDataObject(XML_FILE_PATH);
    call_user_func(COMMAND_MAPPING[$command], $sku, $qty, $xmlCart);
    putContentToXmlFile(formatXml($xmlCart), XML_FILE_PATH);
}

function checkCommandName(string $commandName): bool
{
    $isValid = true;

    if (!preg_match('/^(add|remove)$/', $commandName)) {
        $isValid = false;
    }

    return $isValid;
}

function launchCli(): void
{
    $usageMessage = sprintf("Incorrect input. Usage:\n%s <add|remove> <sku> <qty>", basename($_SERVER['PHP_SELF']));
    if ($_SERVER['argc'] < 4 || $_SERVER['argc'] > 4) {
        throw new Exception($usageMessage);
    }

    [$command, $sku, $qty] = [$_SERVER['argv'][1], $_SERVER['argv'][2], $_SERVER['argv'][3]];

    if (!checkCommandName($command)) {
        throw new Exception($usageMessage);
    }

    $errorMessages = [];
    if (!validateValues($sku, $qty, $errorMessages)) {
        throw new Exception('Incorrect values: ' . PHP_EOL .implode(PHP_EOL, $errorMessages));
    }

    processRequest($command, $sku, $qty);
}

function processPostRequest(): void
{
    $requestUri = explode('/', $_SERVER['REQUEST_URI']);
    $command =  array_pop($requestUri);

    if(!checkCommandName($command)) {
        http_response_code(404);
        return;
    }

    $errorMessages = [];
    [$sku, $qty] = [$_POST['sku'] ?: '', $_POST['qty'] ?: ''];
    if (!validateValues($sku, $qty, $errorMessages)) {
        http_response_code(400);
        echo implode(PHP_EOL, $errorMessages);
        return;
    }

    processRequest($command, $sku, $qty);
}

function launchWeb(): void
{
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        header('Content-type: text/xml');
        echo formatXml(getXmlDataObject(XML_FILE_PATH));
        return;
    }

    if ($method === 'POST') {
        processPostRequest();
        return;
    }

    http_response_code(400);
}

function main(): void
{
    try {
        if (php_sapi_name() == 'cli'){
            launchCli();
        }
        launchWeb();
    } catch (\Exception $e) {
        echo $e->getMessage(), PHP_EOL;
    }
}

main();