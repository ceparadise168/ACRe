<?php 

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $bulider, array $options)
    {
        $userNameArray = [
            'required' => true,
            'empty_data' => 'your name'
        ];
        $msgArray = [
            'required' => true,
            'empty_data' => 'write something'
        ];

        $bulider->add('UserName', null, $userNameArray)
                ->add('Msg', null, $msgArray);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolverArray = ['data_class' => 'AppBundle\Entity\Message'];
        $resolver->setDefaults($resolverArray);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appBundle_Message';
    }
}
