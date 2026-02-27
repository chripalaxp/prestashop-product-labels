{*
* Product Labels - extra field in product form (backoffice)
*}
<div class="panel product-labels-panel">
	<h3><i class="icon icon-tag"></i> {l s='Product Label' mod='product_labels'}</h3>
	<div class="form-group">
		<label class="control-label col-lg-3">{l s='Label' mod='product_labels'}</label>
		<div class="col-lg-9">
			<select name="product_labels_label" id="product_labels_label" class="form-control fixed-width-lg">
				{foreach from=$product_labels_options key=key item=label}
					<option value="{$key|escape:'html':'UTF-8'}"{if $product_labels_current == $key} selected="selected"{/if}>{$label|escape:'html':'UTF-8'}</option>
				{/foreach}
			</select>
			<p class="help-block">{l s='Choose a label to display on this product in the front office.' mod='product_labels'}</p>
		</div>
	</div>
</div>
