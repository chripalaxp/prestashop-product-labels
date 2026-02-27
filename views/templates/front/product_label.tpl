{*
* Product Labels - label display on product list and product page (white text, red background)
*}
{if $product_labels_text}
	<div class="product-labels-badge product-labels-{$product_labels_type|escape:'html':'UTF-8'}">
		<span class="product-labels-text">{$product_labels_text|escape:'html':'UTF-8'}</span>
	</div>
{/if}
