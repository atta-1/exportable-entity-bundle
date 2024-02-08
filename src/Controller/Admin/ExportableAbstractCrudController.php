<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Controller\Admin;

use Atta\ExportableEntityBundle\Message\EntityDataExportMessage;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class ExportableAbstractCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request->query->has('filters')) {
            $exportAction = Action::new('Export')->createAsGlobalAction();
            $exportAction->linkToCrudAction('export');
            $actions->add(Crud::PAGE_INDEX, $exportAction);
        }

        return $actions;
    }

    public function export(AdminContext $context): Response
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filtersConfig = $context->getCrud()?->getFiltersConfig();
        $searchDto = $context->getSearch();
        if ($filtersConfig === null || $searchDto === null) {
            throw new \RuntimeException(sprintf('Controller %s not ready', self::class));
        }
        $filters = $this->container->get(FilterFactory::class)->create($filtersConfig, $fields, $context->getEntity());

        $queryBuilder = $this->createIndexQueryBuilder($searchDto, $context->getEntity(), $fields, $filters);

        $date = new \DateTime();
        /** @var class-string $className */
        $className = static::getEntityFqcn();

        $reflectionClass = (new \ReflectionClass($className))->getShortName();
        /** @var string[] $explodedClassName */
        $explodedClassName = preg_split('/(?=[A-Z])/', $reflectionClass, -1, PREG_SPLIT_NO_EMPTY);
        $filename = sprintf(
            '%s-%s.csv',
            implode('-', array_map(fn (string $part) => mb_strtolower($part), $explodedClassName)),
            $date->format('Y-m-d-H-i'),
        );

        $this->messageBus->dispatch(
            $this->prepareEntityDataExportMessage($className, $queryBuilder, $filename),
        );

        $this->addFlash('success', sprintf(
            '`%s` export is queued and will be ready soon. Filename - %s',
            implode(' ', $explodedClassName),
            $filename,
        ));

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->generateUrl(),
        );
    }

    abstract protected function prepareEntityDataExportMessage(string $className, QueryBuilder $queryBuilder, string $filename): EntityDataExportMessage;
}
