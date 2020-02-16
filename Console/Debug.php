<?php

namespace DataCue\MagentoModule\Console;

use DataCue\MagentoModule\Modules\Product;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;

class Debug extends Command
{
    /**
     * @var ResourceConnection $resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    private $connection;

    /**
     * @var InputInterface $input
     */
    private $input;

    /**
     * @var OutputInterface $output
     */
    private $output;

    public function __construct(ResourceConnection $resource)
    {
        parent::__construct();

        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    protected function configure()
    {
        $this->setName('datacue:debug');
        $this->setDescription('DataCue command line');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('DataCue Debug');
        $this->input = $input;
        $this->output = $output;
        $this->generateProductsInfo();
    }

    private function generateProductsInfo()
    {
        $table = $this->resource->getTableName('catalog_product_website');
        $productEntities = $this->connection->fetchAll("SELECT `product_id` FROM `" . $table . "`");
        $productIds = array_map(function ($item) {
            return $item['product_id'];
        }, $productEntities);

        $file = fopen(static::getDebugFile(),"w");

        foreach ($productIds as $id) {
            $product = Product::getProductById($id);
            $parentProduct = Product::getParentProduct($id);
            if (is_null($parentProduct)) {
                $variantIds = Product::getVariantIds($product->getId());
                if (count($variantIds) === 0) {
                    $data = Product::buildProductForDataCue($product, true);
                    fwrite($file, json_encode($data) . "\n");
                } else {
                    foreach ($variantIds as $vId) {
                        $variant = Product::getProductById($vId);
                        if ($variant) {
                            $data = Product::buildVariantForDataCue($product, $variant, true);
                            fwrite($file, json_encode($data) . "\n");
                        }
                    }
                }
            }
        }
        fclose($file);
    }

    private static function getDebugFile()
    {
        $date = date('YmdHis');
        return __DIR__ . "/../datacue-debug-$date.log";
    }
}