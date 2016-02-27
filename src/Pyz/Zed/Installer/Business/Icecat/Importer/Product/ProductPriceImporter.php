<?php

namespace Pyz\Zed\Installer\Business\Icecat\Importer\Product;

use Orm\Zed\Price\Persistence\SpyPriceProduct;
use Orm\Zed\Price\Persistence\SpyPriceProductQuery;
use Pyz\Zed\Installer\Business\Exception\PriceTypeNotFoundException;
use Pyz\Zed\Installer\Business\Icecat\AbstractIcecatImporter;
use Pyz\Zed\Installer\Business\Icecat\IcecatLocaleManager;
use Pyz\Zed\Stock\Business\StockFacadeInterface;
use Spryker\Shared\Library\Reader\Csv\CsvReaderInterface;
use Spryker\Zed\Price\Persistence\PriceQueryContainerInterface;
use Spryker\Zed\Product\Persistence\ProductQueryContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductPriceImporter extends AbstractIcecatImporter
{

    const SKU = 'sku';
    const PRODUCT_ID = 'product_id';
    const VARIANT_ID = 'variantId';
    const PRICE = 'price';
    const PRICE_TYPE = 'price_type';

    /**
     * @var string
     */
    protected $dataDirectory;

    /**
     * @var \Pyz\Zed\Stock\Business\StockFacadeInterface
     */
    protected $stockFacade;

    /**
     * @var \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected $productQueryContainer;

    /**
     * @var \Spryker\Zed\Price\Persistence\PriceQueryContainerInterface
     */
    protected $priceQueryContainer;

    /**
     * @var \SplFileObject
     */
    protected $priceCsvFile;

    /**
     * @var array
     */
    protected $priceColumns;

    /**
     * @var
     */
    protected $priceTotal;

    /**
     * @var array
     */
    protected $priceTypesCache = [];

    /**
     * @param \Spryker\Shared\Library\Reader\Csv\CsvReaderInterface $csvReader
     * @param \Pyz\Zed\Installer\Business\Icecat\IcecatLocaleManager $localeManager
     */
    public function __construct(CsvReaderInterface $csvReader, IcecatLocaleManager $localeManager, $dataDirectory)
    {
        parent::__construct($csvReader, $localeManager);
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * @param \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface $productQueryContainer
     */
    public function setProductQueryContainer(ProductQueryContainerInterface $productQueryContainer)
    {
        $this->productQueryContainer = $productQueryContainer;
    }

    /**
     * @param \Pyz\Zed\Stock\Business\StockFacadeInterface $stockFacade
     */
    public function setStockFacade(StockFacadeInterface $stockFacade)
    {
        $this->stockFacade = $stockFacade;
    }

    /**
     * @param \Spryker\Zed\Price\Persistence\PriceQueryContainerInterface $priceQueryContainer
     */
    public function setPriceQueryContainer(PriceQueryContainerInterface $priceQueryContainer)
    {
        $this->priceQueryContainer = $priceQueryContainer;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Product Price';
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        $query = SpyPriceProductQuery::create();
        return $query->count() > 0;
    }

    public function importOne(array $data)
    {
        $price = $this->getPriceValue();

        $productAbstract = $this->productQueryContainer
            ->queryProductAbstractBySku($price[self::SKU])
            ->findOne();

        if (!$productAbstract) {
            return;
        }

        if (!array_key_exists($price[self::PRICE_TYPE], $this->priceTypesCache)) {
            $priceTypeQuery = $this->priceQueryContainer->queryPriceType($price[self::PRICE_TYPE]);
            $priceType = $priceTypeQuery->findOne();
            if (!$priceType) {
                throw new PriceTypeNotFoundException($price[self::PRICE_TYPE]);
            }

            $priceTypesCache[$price[self::PRICE_TYPE]] = $priceType;
        }
        else {
            $priceType = $this->priceTypesCache[$price[self::PRICE_TYPE]];
        }

        $entity = new SpyPriceProduct();

        $entity
            ->setPrice($price[self::PRICE])
            ->setPriceType($priceType)
            ->setFkProductAbstract($productAbstract->getIdProductAbstract())
            ->setFkProduct($productAbstract->getIdProductAbstract());

        $entity->save();

    }

    /**
     * @return array
     */
    protected function getPriceValue()
    {
        $default = [
            self::SKU => null,
            self::VARIANT_ID => 1,
            self::PRICE => 0,
            self::PRICE_TYPE => 'DEFAULT',
        ];

        if ($this->priceCsvFile->eof()) { //TODO add this to csvReader
            return $default;
        }

        return $this->csvReader->read();
    }

    /**
     * @return void
     */
    protected function before()
    {
        $this->priceCsvFile = $this->csvReader->load($this->dataDirectory . '/prices.csv')->getFile();
        $this->priceColumns = $this->csvReader->getColumns();
        $this->priceTotal = $this->csvReader->getTotal();

        $this->priceCsvFile->rewind();
    }

    /**
     * @param string|int $variant
     *
     * @return bool
     */
    protected function hasVariants($variant)
    {
        return (int)$variant > 1;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function format(array $data)
    {
        return $data;
    }


}
