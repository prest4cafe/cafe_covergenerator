<div class="container">
    <div class="panel">

   {foreach from=$langs item=lang}
    <h2>{$lang.name}</h2>
        <div class="row">

{*download="{Tools::str2url()}*}
        {assign var=cover value=CoverGenerator::getObjectByIdLang($obj->id,$lang.id_lang)}

      

                <div class="col-md-4">
                <p>Miniature:</p>
                    <a href="{$cover->_path_mini}{$cover->path_image_mini}" download="{$cover->path_image_mini}">
                        <img style="max-height:220px;" class="img-responsive" src="{$cover->_path_mini}{$cover->path_image_mini}"></a>
                    <p><a href="{$cover->_path_mini}{$cover->path_image_mini}" download="{$cover->path_image_mini}"><i
                                class="material-icons">arrow_downward</i>download</a></p>

                </div>
                <div class="col-md-8">
                <p>Couverture:</p>
                    <a href="{$cover->_path_cover}{$cover->path_image_cover}" download="{$cover->path_image_cover}">
                        <img style="max-height:220px;" class="img-responsive" src="{$cover->_path_cover}{$cover->path_image_cover}"></a>
                    <p><a href="{$cover->_path_cover}{$cover->path_image_cover}" download="{$cover->path_image_cover}"><i
                                class="material-icons">arrow_downward</i>download</a></p>
                </div>
        </div>
    {/foreach}


    </div>
</div>