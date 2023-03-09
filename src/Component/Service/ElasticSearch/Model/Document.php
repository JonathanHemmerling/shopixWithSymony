<?php

declare(strict_types=1);

namespace App\Component\Service\ElasticSearch\Model;

use App\Component\Productstorage\Business\Model\ProductStorage;
use App\DTO\ProductsDataTransferObject;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Elastica\Aggregation\Terms;
use Elastica\Client;
use Elastica\Query;
use Elastica\Search;

class Document
{
    private const BULK_SIZE = 2000;
    public function __construct(private readonly CategoryRepository $categoryRepository, private readonly Client $client, private readonly ProductsRepository $productsRepository)
    {
    }

    public function delete():void
    {
        $index = $this->client->getIndex('my_index');
        $index->create([], true);
        $index->refresh();
    }

    public function get(int $id)
    {
        $results = $this->getAll();
        $result = $results[(string)$id];
        return $result;
    }
    public function getAll()
    {
        $search = new Search($this->client);
        $resultSet = $search->search();
        $documentArray = [];
        foreach ($resultSet as $result)
        {
            $document = $result->getDocument();
            $documentArray[$document->getId()] = $document->getData();
        }
        return $documentArray;
    }

    public function getAllCategorys()
    {
        $search = new Search($this->client);
        $term = new Terms('category');
        $term->setField('category');
        $query = new Query();
        $query->addAggregation($term);
        $resultSet = $search->search();
        $result = $resultSet->getAggregation('category');
        dd($result);
        return $result;
    }
    public function getAllProducts(string $categoryName)
    {
        $search = new Search($this->client);
        $term = new Terms($categoryName);
        $term->setField('category');
        $query = new Query();
        $query->addAggregation($term);
        //dd($query);
        dd($search);
        $resultSet = $search->search();
        $result = $resultSet->getAggregations();
        dd($result);
        return $result;
    }


    public function add(): void
    {
        $elasticaType = $this->client->getIndex('my_index');
        $products = $this->productsRepository->findAll();
        $data = [];
        foreach ($products as $product) {
            $articleDocument = new \Elastica\Document(
                (string)$product->getId(),
                [
                    'articleNumber' => $product->getArticleNumber(),
                    'productName' => $product->getProductName(),
                    'price' => $product->getPrice(),
                    'category' => $product->getCategory()->getName(),
                    'description' => $product->getDescription(),
                    'attributes' => $product->getAttribute()->getValues(),
                ]
            );
            $data[] = $articleDocument;
            if (count($data) > self::BULK_SIZE) {
                $elasticaType->addDocuments($data);
                $data = [];
            }
        }

        if (!empty($data)) {
            $elasticaType->addDocuments($data);
        }

        $elasticaType->refresh();
        }

}