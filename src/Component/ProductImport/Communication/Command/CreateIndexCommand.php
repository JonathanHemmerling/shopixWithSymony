<?php

namespace App\Component\ProductImport\Communication\Command;

use App\Repository\PostRepository;
use App\Repository\ProductsRepository;
use Elastica\Client;
use Elastica\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:elasticsearch:create-index',
    description: 'Build new index from scratch and populate',
    hidden: false,
    aliases: ['app:elasticsearch:create-index']
)]
class CreateIndexCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Build new index from scratch and populate.');
    }

    public function __construct(
        private readonly Client $client,
        private readonly ProductsRepository $productsRepository
    ) {
        parent::__construct('Name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexBuilder = $this->client->getIndexBuilder();
        $newIndex = $indexBuilder->createIndex('products');
        $indexer = $this->client->getIndexer();

        $allProducts = $this->productsRepository->createQueryBuilder('products')->getQuery()->iterate();
        foreach ($allProducts as $product) {
            $product = $product[0];
            $indexer->scheduleIndex($newIndex, new Document($product->getId(), $product->toModel()));
        }

        $indexer->flush();

        $indexBuilder->markAsLive($newIndex, 'products');
        $indexBuilder->speedUpRefresh($newIndex);
        $indexBuilder->purgeOldIndices('products');

        return Command::SUCCESS;
    }
}