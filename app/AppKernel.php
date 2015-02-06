<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new PunkAve\FileUploaderBundle\PunkAveFileUploaderBundle(),
            //new Beryllium\CacheBundle\BerylliumCacheBundle(),
            //new Aequasi\Bundle\MemcachedBundle\AequasiMemcachedBundle(),
            //new winzou\CacheBundle\winzouCacheBundle(),
            new Maxmind\Bundle\GeoipBundle\MaxmindGeoipBundle(),
            //new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),
            new Slik\DompdfBundle\SlikDompdfBundle()
            //new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Acme\IndexBundle\AcmeIndexBundle();
            $bundles[] = new Acme\AuthBundle\AcmeAuthBundle();
            $bundles[] = new Acme\SecureBundle\AcmeSecureBundle();
            $bundles[] = new Acme\AdminBundle\AcmeAdminBundle();
            //$bundles[] = new DMS\Bundle\FilterBundle\DMSFilterBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function init()
    {
        date_default_timezone_set( 'Europe/Moscow' );
        parent::init();
    }
}
