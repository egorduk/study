<?php

namespace Acme\RssBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class ViewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder->add('sourceId', 'entity', array('label'=>'Select source:', 'class' => 'Acme\\TestBundle\\Entity\\Source', 'property' => 'name','empty_value' => 'Choose a source', 'required' => false))
                ->add('Delete', 'submit', array(
                'attr' => array('class' => 'symfony-button-grey')));*/

        $builder->add('Delete', 'button', array(
                'attr' => array('class' => 'btn btn-danger')))
                ->add('Save', 'button', array(
                    'attr' => array('class' => 'btn btn-success', 'id' => '')));
    }

    public function getName()
    {
        return 'viewForm';
    }
}
