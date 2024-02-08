<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Controller\Admin;

use Atta\ExportableEntityBundle\Entity\DataExport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DataExportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DataExport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormThemes(['admin/crud/form_theme.html.twig'])
            ->setEntityLabelInPlural('Excel Exports')
            ->setEntityLabelInSingular('Excel Export')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(50)
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $downloadAction = Action::new('Download')->linkToCrudAction('download')->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->add(Crud::PAGE_INDEX, $downloadAction)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
        yield TextField::new('filename', 'Filename');
        yield TextField::new('status.value', 'Status');
        yield TextField::new('exceptionMessage', 'Exception Message');
        yield DateTimeField::new('createdAt', 'CreatedAt');
    }

    public function download(AdminContext $context): Response
    {
        $entity = $context->getEntity()->getInstance();
        assert($entity instanceof DataExport);
        $downloadUrl = $entity->getDownloadUrl();
        if ($downloadUrl === null) {
            throw new NotFoundHttpException('Not found download url');
        }

        return new RedirectResponse($downloadUrl);
    }
}
