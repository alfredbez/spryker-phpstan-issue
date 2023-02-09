<?php

namespace Pyz\Zed\CategoryDataImport\Business\QueryExpander;

use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryNodeTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap;
use Orm\Zed\Locale\Persistence\Map\SpyLocaleTableMap;
use Orm\Zed\ProductCategory\Persistence\Map\SpyProductCategoryTableMap;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Formatter\ArrayFormatter;

class FooCategoryQueryExpander
{
    /**
     * @var \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery
     */
    protected $productCategoryQuery;

    /**
     * @param \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery $productCategoryQuery
     */
    public function __construct(SpyProductCategoryQuery $productCategoryQuery)
    {
        $this->productCategoryQuery = $productCategoryQuery;
    }

    /**
     * @param array $dataRows
     *
     * @return array
     */
    public function expand(array $dataRows): array
    {
        $categoriesQuery = $this->productCategoryQuery
            ->filterByFkProductAbstract_In($dataRows)
                ->useSpyCategoryQuery()
                    ->joinNode()
                    ->filterByIsActive(true)
                    ->filterByIsSearchable(true)
                    ->useAttributeQuery(null, Criteria::LEFT_JOIN)
                        ->useLocaleQuery()
                            ->filterByIsActive(true)
                        ->endUse()
                    ->endUse()
                ->endUse();

        // These lines in combination with the use*Query methods above make the analysis very slow
        $categoriesQuery = $categoriesQuery->withColumn(SpyProductCategoryTableMap::COL_FK_PRODUCT_ABSTRACT, ProductAbstractTransfer::ID_PRODUCT_ABSTRACT);
        /*
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryTableMap::COL_ID_CATEGORY, CategoryTransfer::ID_CATEGORY);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryTableMap::COL_IS_ACTIVE, CategoryTransfer::IS_ACTIVE);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryTableMap::COL_IS_SEARCHABLE, CategoryTransfer::IS_SEARCHABLE);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryTableMap::COL_CATEGORY_KEY, CategoryTransfer::CATEGORY_KEY);
        $categoriesQuery = $categoriesQuery->withColumn(SpyLocaleTableMap::COL_LOCALE_NAME, CategoryLocalizedAttributesTransfer::LOCALE);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryAttributeTableMap::COL_NAME, CategoryLocalizedAttributesTransfer::NAME);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryAttributeTableMap::COL_META_TITLE, CategoryLocalizedAttributesTransfer::META_TITLE);
        $categoriesQuery = $categoriesQuery->withColumn(SpyProductCategoryTableMap::COL_PRODUCT_ORDER, CategoryTransfer::ID_CMS_BLOCKS);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_FK_CATEGORY, NodeTransfer::FK_CATEGORY);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE, NodeTransfer::ID_CATEGORY_NODE);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_IS_MAIN, NodeTransfer::IS_MAIN);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_IS_ROOT, NodeTransfer::IS_ROOT);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_NODE_ORDER, NodeTransfer::NODE_ORDER);
        $categoriesQuery = $categoriesQuery->withColumn(SpyCategoryNodeTableMap::COL_FK_PARENT_CATEGORY_NODE, NodeTransfer::FK_PARENT_CATEGORY_NODE);
        $categoriesQuery = $categoriesQuery->setFormatter(new ArrayFormatter());
        */

        return [];
    }
}
