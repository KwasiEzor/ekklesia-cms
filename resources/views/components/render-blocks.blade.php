@props(['blocks'])

<div class="page-content">
    @foreach($blocks as $block)
        @php
            $type = $block['type'];
            $data = $block['data'];
        @endphp

        @switch($type)
            @case('hero')
                <x-blocks.hero :title="$data['title']" :subtitle="$data['subtitle'] ?? null" :imageUrl="$data['image_url']" :ctaLabel="$data['cta_label'] ?? null" :ctaUrl="$data['cta_url'] ?? null" />
                @break

            @case('heading')
                <x-blocks.heading :level="$data['level']" :content="$data['content']" />
                @break

            @case('rich_text')
                <x-blocks.rich_text :body="$data['body']" />
                @break

            @case('features')
                <x-blocks.features :items="$data['items']" />
                @break

            @case('image')
                <x-blocks.image :url="$data['url']" :alt="$data['alt'] ?? ''" :caption="$data['caption'] ?? null" />
                @break

            @case('faq')
                <x-blocks.faq :items="$data['items']" />
                @break

            @case('video')
                <x-blocks.video :url="$data['url']" :caption="$data['caption'] ?? null" />
                @break

            @case('call_to_action')
                <x-blocks.call_to_action :label="$data['label']" :url="$data['url']" :style="$data['style'] ?? 'primary'" />
                @break

            @case('quote')
                <x-blocks.quote :text="$data['text']" :attribution="$data['attribution'] ?? null" />
                @break

            @case('testimonials')
                <x-blocks.testimonials :items="$data['items']" />
                @break

            @case('contact_form')
                <x-blocks.contact_form :title="$data['title']" :description="$data['description'] ?? null" :emailTo="$data['email_to'] ?? null" />
                @break
        @endswitch
    @endforeach
</div>
