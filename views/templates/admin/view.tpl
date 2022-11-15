<div class="container">
    <div class="panel">

        <div class="row">
            {foreach from=$covers_mini item=mini}
                <div class="col-md-6">

                    <a href="{$obj->_path_mini}{$mini}" download="mini">
                        <img class="img-responsive" src="{$obj->_path_mini}{$mini}"></a>
                    <p><a href="{$obj->_path_mini}{$mini}" download="{Tools::str2url($mini)}"><i
                                class="material-icons">arrow_downward</i>download</a></p>

                </div>

            {/foreach}

        </div>
        <hr>
        <div class="row">
            {foreach from=$covers item=cover}
                <div class="col-md-6">

                    <a href="{$obj->_path_cover}{$cover}" download="cover">
                        <img class="img-responsive" src="{$obj->_path_cover}{$cover}"></a>
                    <p><a href="{$obj->_path_cover}{$cover}" download="{Tools::str2url($cover)}"><i
                                class="material-icons">arrow_downward</i>download</a></p>

                </div>


            {/foreach}
        </div>

    </div>
</div>