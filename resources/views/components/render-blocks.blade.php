@props(['blocks'])

<div class="page-content">
    @foreach($blocks as $block)
        @php
            $type = $block['type'];
            $data = $block['data'];
        @endphp

        @switch($type)
            @case('hero')
                <x-blocks.hero 
                    :slides="$data['slides'] ?? []" 
                    :isCarousel="$data['is_carousel'] ?? false" 
                    :autoplaySpeed="$data['autoplay_speed'] ?? 5000"
                    :transitionType="$data['transition_type'] ?? 'fade'"
                    :title="$data['title'] ?? null" 
                    :subtitle="$data['subtitle'] ?? null" 
                    :imageUrl="$data['image_url'] ?? null" 
                    :ctaLabel="$data['cta_label'] ?? null" 
                    :ctaUrl="$data['cta_url'] ?? null" 
                />
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
                <x-blocks.testimonials 
                    :items="$data['items']" 
                    :isCarousel="$data['is_carousel'] ?? false" 
                    :autoplaySpeed="$data['autoplay_speed'] ?? 5000" 
                />
                @break

            @case('contact_form')
                <x-blocks.contact_form :title="$data['title']" :description="$data['description'] ?? null" :emailTo="$data['email_to'] ?? null" />
                @break

            @case('sermon_feed')
                <x-blocks.sermon_feed 
                    :title="$data['title'] ?? 'Latest Sermons'" 
                    :limit="$data['limit'] ?? 3" 
                    :seriesId="$data['series_id'] ?? null" 
                    :viewStyle="$data['view_style'] ?? 'grid'"
                    :showDownloadNotes="$data['show_download_notes'] ?? false"
                />
                @break

            @case('staff_directory')
                <x-blocks.staff_directory :title="$data['title']" :department="$data['department'] ?? 'all'" />
                @break

            @case('giving_cta')
                <x-blocks.giving_cta 
                    :title="$data['title']" 
                    :description="$data['description'] ?? null" 
                    :buttonLabel="$data['button_label'] ?? 'Give Online Now'" 
                    :fundId="$data['fund_id'] ?? null"
                    :showQuickGive="$data['show_quick_give'] ?? false"
                    :quickGiveAmounts="$data['quick_give_amounts'] ?? '10, 20, 50, 100'"
                />
                @break

            @case('events_feed')
                <x-blocks.events_feed :title="$data['title']" :limit="$data['limit'] ?? 3" :showPast="$data['show_past'] ?? false" />
                @break

            @case('live_stream')
                <x-blocks.live_stream :title="$data['title']" :streamUrl="$data['stream_url'] ?? null" :alwaysShow="$data['always_show'] ?? true" />
                @break

            @case('countdown_timer')
                <x-blocks.countdown_timer :title="$data['title']" :targetDate="$data['target_date']" :ctaLabel="$data['cta_label'] ?? null" :ctaUrl="$data['cta_url'] ?? null" />
                @break

            @case('columns')
                <x-blocks.columns :layout="$data['layout']" :columns="$data['columns']" />
                @break

            @case('tabs')
                <x-blocks.tabs :items="$data['items']" />
                @break

            @case('spacer')
                <x-blocks.spacer :size="$data['size'] ?? 'medium'" />
                @break

            @case('divider')
                <x-blocks.divider :style="$data['style'] ?? 'solid'" />
                @break

            @case('gallery')
                <x-blocks.gallery :title="$data['title'] ?? null" :galleryId="$data['gallery_id']" :layout="$data['layout'] ?? 'grid'" />
                @break

            @case('logo_cloud')
                <x-blocks.logo_cloud 
                    :title="$data['title'] ?? null" 
                    :logos="$data['logos'] ?? []" 
                    :isCarousel="$data['is_carousel'] ?? true" 
                    :autoplaySpeed="$data['autoplay_speed'] ?? 5000"
                />
                @break

            @case('newsletter_signup')
                <x-blocks.newsletter_signup :title="$data['title']" :description="$data['description'] ?? null" :buttonLabel="$data['button_label'] ?? 'Subscribe'" />
                @break

            @case('map')
                <x-blocks.map :title="$data['title'] ?? null" :campusId="$data['campus_id'] ?? null" :address="$data['address'] ?? null" />
                @break
        @endswitch
    @endforeach
</div>
