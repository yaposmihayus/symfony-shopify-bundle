<?php

namespace CodeCloud\Bundle\ShopifyBundle\Command;

use CodeCloud\Bundle\ShopifyBundle\Model\ShopifyStoreManagerInterface;
use CodeCloud\Bundle\ShopifyBundle\Service\WebhookCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebhooksCommand extends Command
{
    /**
     * @var WebhookCreator
     */
    private $webhookCreator;

    /**
     * @var ShopifyStoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $topics = [];

    /**
     * @param WebhookCreator $webhookCreator
     * @param ShopifyStoreManagerInterface $storeManager
     * @param array $topics
     */
    public function __construct(WebhookCreator $webhookCreator, ShopifyStoreManagerInterface $storeManager, array $topics)
    {
        $this->webhookCreator = $webhookCreator;
        $this->storeManager = $storeManager;
        $this->topics = $topics;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('codecloud:shopify:webhooks')
            ->setDescription('Interact with Shopify Webhooks')
            ->addArgument('store', InputArgument::REQUIRED, 'The store to install webhooks in')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete existing webhooks')
            ->addOption('list', null, InputOption::VALUE_NONE, 'List existing webhooks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = $this->storeManager->findStoreByName($input->getArgument('store'));

        if (!$store) {
            throw new \StoreNotFoundException($input->getArgument('store'));
        }

        if ($input->getOption('list')) {
            $output->writeln(print_r($this->webhookCreator->listWebhooks($store), true));

            return;
        }

        if ($input->getOption('delete')) {
            $this->webhookCreator->deleteAllWebhooks($store);
            $output->writeln('Webhooks deleted');

            return;
        }

        if (empty($this->topics)) {
            throw new \LogicException('No webhook topics configured');
        }

        $this->webhookCreator->createWebhooks($store, $this->topics);
        $output->writeln('Webhooks created');
    }
}
