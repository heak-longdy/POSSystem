<x-website::r2form.input :label="__('package.form.create.title')" name="name" :value="request('id') ? $data->name : 0"
    placeholder="Enter name" />
<x-website::r2form.textarea label="Description" name="des" :value="request('id') ? $data->des : null" placeholder="Enter price"
    rows="5" />
<x-website::r2form.row row="2">
    <x-website::r2form.input />
    <x-website::r2form.select />
</x-website::r2form.row>
<x-website::r2form.select label="Status" name="status" :options="json_encode($status)" valueField="value" nameField="name"
    :selected="request('id') ?  $data->status : 1" />
<x-website::r2form.photo label="Thumbnail" name="thumbnail" :value="request('id') ? $data->thumbnail ?? null : null"
    :path="request('id') ? asset('uploads/package/' . $data->thumbnail) : null" />
