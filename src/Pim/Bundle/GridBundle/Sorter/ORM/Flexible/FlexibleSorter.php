<?php

namespace Pim\Bundle\GridBundle\Sorter\ORM\Flexible;

use Oro\Bundle\GridBundle\Sorter\ORM\Sorter;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;

/**
 * Flexible sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleSorter extends Sorter
{
    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleRegistry;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @param FlexibleManagerRegistry $flexibleRegistry
     */
    public function __construct(FlexibleManagerRegistry $flexibleRegistry)
    {
        $this->flexibleRegistry = $flexibleRegistry;
    }

    /**
     * @param FieldDescriptionInterface $field
     * @param string                    $direction
     *
     * @throws \LogicException
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        parent::initialize($field, $direction);

        $flexibleEntityName = $field->getOption('flexible_name');
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity sorter must have flexible entity name.');
        }

        $this->flexibleManager = $this->flexibleRegistry->getManager($flexibleEntityName);
    }

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param string|null         $direction
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        $this->setDirection($direction);
        $queryBuilder = $queryInterface->getQueryBuilder();

        $flexibleQB = new FlexibleQueryBuilder(
            $queryBuilder,
            $this->flexibleManager->getLocale(),
            $this->flexibleManager->getScope()
        );
        $attributeCode = $this->getField()->getFieldName();
        $attribute = $this->flexibleManager->getAttributeRepository()
            ->findOneBy(array('code' => $attributeCode, 'entityType' => $this->flexibleManager->getFlexibleName()));

        $flexibleQB->addAttributeOrderBy($attribute, $direction);
    }
}
