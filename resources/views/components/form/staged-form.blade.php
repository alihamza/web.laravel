<?php /** @var \Dms\Web\Laravel\Http\ModuleContext $moduleContext */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Form\FormRenderingContext $renderingContext */ ?>
<?php /** @var \Dms\Core\Module\IAction $action */ ?>
<?php /** @var \Dms\Core\Form\IStagedForm $stagedForm */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Form\FormRenderer $formRenderer */ ?>
<?php /** @var array $hiddenValues */ ?>
<div class="dms-staged-form-container">
    <div
            data-reload-form-url="{{ $moduleContext->getUrl('action.form', array_merge([$actionName], $renderingContext->getObject() ? [$renderingContext->getObjectId()] : [])) }}"
            data-action="{{ $moduleContext->getUrl('action.run', [$actionName]) }}"
            data-method="post"
            data-enctype="multipart/form-data"
            class="dms-staged-form form-horizontal"
    >
        {!! csrf_field() !!}

        @if($hiddenValues)
            <div class="dms-form-stage-container loaded hidden">
                <div class="dms-form-stage dms-form-stage-known-data">
                    @foreach($hiddenValues ?? [] as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}"/>
                    @endforeach
                </div>
            </div>
        @endif

        <?php $currentData = [] ?>
        @for ($stageNumber = 1; $stageNumber <= $stagedForm->getAmountOfStages(); $stageNumber++)
            <?php $absoluteStageNumber =  $stageNumber + ($initialStageNumber ?? 1) - 1 ?>
            <?php $renderingContext->setCurrentStageNumber($absoluteStageNumber) ?>
            <?php $stage = $stagedForm->getStage($stageNumber) ?>
            @if ($stage instanceof \Dms\Core\Form\Stage\IndependentFormStage)
                <?php $form = $stage->loadForm() ?>
                <div class="dms-form-stage-container loaded">
                    <div class="dms-form-stage" data-stage-number="{{ $stageNumber }}">
                        {!!  $formRenderer->renderFields($renderingContext, $form) !!}
                    </div>
                </div>
                <?php $currentData += $form->getInitialValues() ?>
            @else
                <?php $form = $stagedForm->tryLoadFormForStage($stageNumber, $currentData, true) ?>
                <div class="dms-form-stage-container {{ $form ? 'loaded' : '' }}">
                    <div
                            class="dms-form-stage dms-dependent-form-stage"
                            data-stage-number="{{ $stageNumber }}"
                            data-load-stage-url="{{ $moduleContext->getUrl('action.form.stage', [$actionName, $absoluteStageNumber]) }}"
                            @if($stage->getRequiredFieldNames() !== null)
                            data-stage-dependent-fields="{{ json_encode($stage->getRequiredFieldNames()) }}"
                            data-stage-dependent-fields-stage-map="{{ json_encode($stagedForm->getRequiredFieldGroupedByStagesForStage($stageNumber)) }}"
                            @endif
                    >
                        @if ($form)
                            {!!  $formRenderer->renderFields($renderingContext, $form) !!}
                            <?php $currentData += $form->getInitialValues() ?>
                        @else
                            <div class="row">
                                <div class="col-lg-offset-2 col-lg-10 col-md-offset-3 col-md-9 col-sm-offset-4 col-sm-8">
                                    <p class="help-block">
                                        The following fields are not shown because they require you to enter the values
                                        for the previous fields in
                                        this form.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    @include('dms::partials.spinner')
                </div>
            @endif
        @endfor

        <button class="btn btn-{{ \Dms\Web\Laravel\Util\KeywordTypeIdentifier::getClass($action->getName()) ?? 'primary' }}"
                type="submit">
            {{ \Dms\Web\Laravel\Util\StringHumanizer::title($action->getName()) }}
            <i class="fa fa-arrow-right"></i>
        </button>

    </div>
    @include('dms::partials.spinner')
</div>