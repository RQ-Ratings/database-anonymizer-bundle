<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use WebnetFr\DatabaseAnonymizer\Command\GuessConfigCommand;
use WebnetFr\DatabaseAnonymizer\ConfigGuesser\ConfigGuesser;
use WebnetFr\DatabaseAnonymizer\ConfigGuesser\ConfigWriter;
use WebnetFr\DatabaseAnonymizer\GeneratorFactory\ChainGeneratorFactory;
use WebnetFr\DatabaseAnonymizer\GeneratorFactory\ConstantGeneratorFactory;
use WebnetFr\DatabaseAnonymizer\GeneratorFactory\FakerGeneratorFactory;
use WebnetFr\DatabaseAnonymizerBundle\Command\AnonymizeCommand;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autoconfigure();

    $services->set(ConstantGeneratorFactory::class);
    $services->set(FakerGeneratorFactory::class);
    $services->set(ChainGeneratorFactory::class);

    $services->set(AnonymizeCommand::class)
        ->arg(0, service(ChainGeneratorFactory::class))
        ->tag('console.command', ['command' => 'webnet-fr:anonymizer:anonymize']);

    $services->set(ConfigGuesser::class);
    $services->set(ConfigWriter::class);

    $services->set(GuessConfigCommand::class)
        ->arg(0, service(ConfigGuesser::class))
        ->arg(1, service(ConfigWriter::class))
        ->tag('console.command', ['command' => 'webnet-fr:anonymizer:guess-config']);
};
