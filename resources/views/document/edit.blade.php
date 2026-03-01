
{{Form::model($document, array('route' => array('document.update', encrypt($document->id)), 'method' => 'PUT','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">

        <div class="form-group col-md-6">
            {{Form::label('assign_to',__('Assign To'),array('class'=>'form-label'))}}
            {{Form::select('assign_to',$client,null,array('class'=>'form-control select2'))}}
        </div>
        <div class="form-group  col-md-6">
            {{Form::label('name',__('Name'),array('class'=>'form-label'))}}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter document name')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('category_id',__('Category'),array('class'=>'form-label'))}}
            {{Form::select('category_id',$category,null,array('class'=>'form-control select2','id'=>'category'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('sub_category_id',__('Sub Category'),array('class'=>'form-label'))}}
            <div class="sc_div">
                <select class="form-control select2 sub_category_id" id="sub_category_id" name="sub_category_id">
                    <option value="">{{__('Select Sub Category')}}</option>
                </select>
            </div>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('tages',__('Tages'),array('class'=>'form-label'))}}
            {{Form::select('tages[]',$tages,explode(',',$document->tages),array('class'=>'form-control select2','multiple'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('stage_id',__('Stage'),array('class'=>'form-label'))}}
            {{Form::select('stage_id',$stage_id,null,array('class'=>'form-control select2'))}}
        </div>
        <div class="form-group  col-md-12">
            {{Form::label('description',__('Description'),array('class'=>'form-label'))}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>3))}}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}
<script>
    var baseUrl = "{{ route('category.sub-category', ':id') }}";
</script>
<script src="{{ asset('js/custom/document.js?') }}{{ time() }}"></script>

<script>
    $('#category').trigger('change');
</script>


