<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=uk"></script>

<script src="admin/js/googlemaps.js?2" type="text/javascript"></script>


<div class="controls">
    <div class="fleft">
        <div id="map-canvas" style="width:725px; height:320px" ></div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>

    <div class="google-map-form clearfix">
        <div class="control-group">
            <label class="control-label">Ширина</label>
            <div class="control">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-globe"></i></span>
                    <input class="span2" id="lng" type="text" name="data[lng]" value="{if !empty($data.lng)}{$data.lng}{/if}" />
                </div>
            </div>

            <label class="control-label">Долгота</label>
            <div class="control">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-globe"></i></span>
                    <input class="span2" id="lat" type="text" name="data[lat]" value="{if !empty($data.lat)}{$data.lat}{/if}" />
                </div>
            </div>

            <label class="control-label">Зум</label>
            <div class="control">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-zoom-in"></i></span>
                    <input class="span1" id="zoom" type="text" name="data[zoom]" value="{if !empty($data.zoom)}{$data.zoom}{/if}" />
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="control-group">
            <label class="control-label">Адрес для поиска</label>
            <div class="control">
                <div class="input-prepend">
                    <span class="add-on"><i class=" icon-home"></i></span>
                    <input class="span5 address" id="address" type="text" value="" />
                </div>
            </div>
        </div>
    </div>


</div>

