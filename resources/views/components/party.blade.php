<fieldset @if($num!=0) id="party_{{$name}}_{{ $num }}" @endif>

    @if($num == 0)
        <legend class="border-bottom">{{$name}} Information</legend>
    @else
        <legend class="border-bottom"> Additional {{$name}} #{{ $num }}</legend><button class="btn btn-danger w-100" onclick="removeParty('party_{{$name}}_{{ $num }}')" type="button">Remove</button>
    @endif

    <div class="form-group required pt-3">
        <label class="font-weight-bold " for="{{$name}}[{{ $num }}][name]">{{$name}} Name</label>

        <input autocomplete="off" type="text" class="form-control form-control-user" id="{{$name}}[{{ $num }}][name]" name="{{$name}}[{{ $num }}][name]" required @isset($old['name']) value="{{ $old['name'] }}" @endisset>
    </div>

    <div class="form-group">
        <label class="font-weight-bold " for="{{$name}}[{{ $num }}][attorney]">{{$name}} Attorney </label>
        <select name="{{$name}}[{{ $num }}][attorney]" id="{{$name}}-{{ $num }}-attorney" autocomplete="off"></select>
        <small class="form-text text-muted"><a href="https://jacs-dev.flcourts18.org/attorney-add">Can't find Attorney?</a></small>
    </div>


    <div class="form-group">
        <label class="font-weight-bold" for="{{$name}}[{{ $num }}][address]">Address<small> for attorney, or <span class="font-weight-bold">if not attorney, for the party.</span></small></label>
        <textarea id="{{$name}}[{{ $num }}][address]" name="{{$name}}[{{ $num }}][address]" class="form-control form-control-user" cols="40" rows="2" > @isset($old['address']) {{ $old['address'] }} @endisset</textarea>
    </div>
    <div class="form-group">
        <label class="font-weight-bold " for="{{$name}}[{{ $num }}][tele]">Daytime Telephone #</label>
        <input maxlength="16" class="form-control form-control-user" id="{{$name}}[{{ $num }}]tele" name="{{$name}}[{{ $num }}][tele]" @isset($old['tele']) value="{{ $old['tele'] }}" @endisset>
    </div>


    <div class="form-group">
        <label class="font-weight-bold " for="{{$name}}[{{ $num }}][email]">{{$name}} Email (Separate multiple emails with ';')</label>
        <input type="text" class="form-control form-control-user " id="{{$name}}[{{ $num }}][email]" name="{{$name}}[{{ $num }}][email]" @isset($old['email']) value="{{ $old['email'] }}" @endisset>
        <small>Separate emails with multiple emails with a semicolon.</small>
    </div>

</fieldset>
