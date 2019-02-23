<?php

namespace Enoch\UrlKeys\Model;

class ProductUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
{

	CONST URL_SEPARATOR = "-";

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
	 */
	protected $_productCollectionFactory;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	)
	{
		$this->_productCollectionFactory = $productCollectionFactory;
		parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
	}

	private function checkUrlKeyExists($urlKey, $pid){
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToFilter('url_key', $urlKey);

		return $collection->count() > 0 && $pid !== $collection->getFirstItem()->getId();
	}

	/**
	 * Prepare url key for product
	 *
	 * @param \Magento\Catalog\Model\Product $product
	 * @return string
	 */
	protected function prepareProductUrlKey(\Magento\Catalog\Model\Product $product)
	{
		$urlKey = parent::prepareProductUrlKey($product);

		if($this->checkUrlKeyExists($urlKey, $product->getId())){
			$vanillaUrl = $urlKey;
			$lastPortionPos = strrpos($urlKey, self::URL_SEPARATOR);
			$lastPortion = substr($urlKey, $lastPortionPos);

			if(is_numeric($lastPortion)){
				$vanillaUrl = substr($vanillaUrl, 0, -1);
				$counter = intval($lastPortion);
			}
			else{
				$counter = 0;
				$vanillaUrl .= self::URL_SEPARATOR;
			}

			do{
				$urlKey = $vanillaUrl . $counter++;
			}
			while($this->checkUrlKeyExists($urlKey, $product->getId()));
		}

		return $urlKey;
	}

}