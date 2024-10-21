@if ($Title->HomePage == true)
  <form class="form d-flex flex-column h-100" action="{{ route('updateSMImgHome') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-content flex-grow-1">
      <div class="form-group mb-3">
        <label class="form-label mb-1">{{ __('settings.1STIMAGE') }}</label>
        <div class="custom-file">
          <label class="form-label custom-file-label"
            for="img1">{{ __('settings.Chooseimagefile', ['id' => '1']) }}</label>
          <input type="file" class="form-control" name="img1" id="img1" accept='image/*'>
        </div>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img1title">{{ __('settings.Image') }} 1 -
          {{ __('settings.ARABICTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->ar_title }}" name="ar_img1title" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img1title">{{ __('settings.Image') }} 1 -
          {{ __('settings.ENGLISHTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->title }}" name="img1title" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img1title2">{{ __('settings.Image') }} 1 -
          {{ __('settings.ARABICECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->ar_set1 }}" name="ar_img1title2" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img1title2">{{ __('settings.Image') }} 1 -
          {{ __('settings.ENGLISHSECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->set1 }}" name="img1title2" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img1dsc">{{ __('settings.Image') }} 1 -
          {{ __('settings.ARABICDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->ar_dsc }}" name="ar_img1dsc" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img1dsc">{{ __('settings.Image') }} 1 -
          {{ __('settings.ENGLISHDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->dsc }}" name="img1dsc" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img1url">{{ __('settings.Image') }} 1 -
          {{ __('settings.URLOF') }}</label>
        <input type="text" class="form-control" value="{{ $Img1->url }}" name="img1url" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1">{{ __('settings.2NDIMAGE') }}</label>
        <div class="custom-file">
          <label class="form-label custom-file-label"
            for="img2">{{ __('settings.Chooseimagefile', ['id' => '2']) }}</label>
          <input type="file" class="form-control" name="img2" id="img2" accept='image/*'>
        </div>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img2title">{{ __('settings.Image') }} 2 -
          {{ __('settings.ARABICTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->ar_title }}" id="ar_img2title" name="ar_img2title"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img2title">{{ __('settings.Image') }} 2 -
          {{ __('settings.ENGLISHTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->title }}" name="img2title" id="img2title"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img2title2">{{ __('settings.Image') }} 2 -
          {{ __('settings.ARABICECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->ar_set1 }}" name="ar_img2title2" id="ar_img2title2"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img2title2">{{ __('settings.Image') }} 2 -
          {{ __('settings.ENGLISHSECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->set1 }}" name="img2title2" id="img2title2"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img2dsc">{{ __('settings.Image') }} 2 -
          {{ __('settings.ARABICDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->ar_dsc }}" name="ar_img2dsc" id="ar_img2dsc"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img2dsc">{{ __('settings.Image') }} 2 -
          {{ __('settings.ENGLISHDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->dsc }}" name="img2dsc" id="img2dsc"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img2url">{{ __('settings.Image') }} 2 -
          {{ __('settings.URLOF') }}</label>
        <input type="text" class="form-control" value="{{ $Img2->url }}" name="img2url" id="img2url"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1">{{ __('settings.3RDIMAGE') }}</label>
        <div class="custom-file">
          <input type="file" class="form-control" name="img3" id="img3" accept='image/*'>
          <label class="form-label custom-file-label"
            for="img3">{{ __('settings.Chooseimagefile', ['id' => '3']) }}</label>
        </div>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img3title">{{ __('settings.Image') }} 3 -
          {{ __('settings.ARABICTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->ar_title }}" name="ar_img3title"
          id="ar_img3title" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img3title">{{ __('settings.Image') }} 3 -
          {{ __('settings.ENGLISHTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->title }}" name="img3title" id="img3title"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img3title2">{{ __('settings.Image') }} 3 -
          {{ __('settings.ARABICECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->ar_set1 }}" name="ar_img3title2"
          id="ar_img3title2" required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img3title2">{{ __('settings.Image') }} 3 -
          {{ __('settings.ENGLISHSECONDTITLE') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->set1 }}" name="img3title2" id="img3title2"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ar_img3dsc">{{ __('settings.Image') }} 3 -
          {{ __('settings.ARABICDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->ar_dsc }}" name="ar_img3dsc" id="ar_img3dsc"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img3dsc">{{ __('settings.Image') }} 3 -
          {{ __('settings.ENGLISHDESCRIPTION') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->dsc }}" name="img3dsc" id="img3dsc"
          required>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="img3url">{{ __('settings.Image') }} 3 -
          {{ __('settings.URLOF') }}</label>
        <input type="text" class="form-control" value="{{ $Img3->url }}" name="img3url" id="img3url"
          required>
      </div>
    </div>

    <div class="form-group d-flex justify-content-end mt-auto">
      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
    </div>
  </form>
@endif
