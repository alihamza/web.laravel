<?php /** @var \Dms\Web\Laravel\Renderer\Form\ActionFormRenderer $formRenderer */ ?>
<?php /** @var \Dms\Core\Module\IParameterizedAction $action */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Action\ActionButton[] $actionButtons */ ?>
<?php /** @var array $hiddenValues */ ?>
@extends('dms::template.default')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                @if($actionButtons || $objectLabel)
                    <div class="box-header with-border clearfix">
                        <h3 class="box-title">{{ $objectLabel }}</h3>
                        <div class="pull-right box-tools">
                            @include('dms::package.module.action-buttons', ['actionButtons' => $actionButtons])
                        </div>
                    </div>
                @endif
                <!-- /.box-header -->
                <div class="box-body">
                    {!! $formRenderer->renderActionForm($action, $hiddenValues ?? []) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endsection