import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'Ekklesia CMS',
  description: 'The open-source CMS built for African churches and religious organizations',
  lang: 'en-US',

  head: [
    ['link', { rel: 'icon', href: '/favicon.svg' }],
    ['meta', { name: 'theme-color', content: '#1A3A5C' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'Ekklesia CMS' }],
    ['meta', { property: 'og:description', content: 'The open-source CMS built for African churches and religious organizations' }],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'Ekklesia CMS',

    nav: [
      { text: 'Guide', link: '/guide/introduction' },
      { text: 'Architecture', link: '/architecture/overview' },
      { text: 'Decisions', link: '/architecture/decisions' },
      {
        text: 'v1.0.0-alpha',
        items: [
          { text: 'Changelog', link: '/guide/changelog' },
          { text: 'Roadmap', link: '/guide/roadmap' },
        ]
      }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Introduction', link: '/guide/introduction' },
            { text: 'Why Ekklesia?', link: '/guide/why-ekklesia' },
            { text: 'Quick Start', link: '/guide/quick-start' },
            { text: 'Roadmap', link: '/guide/roadmap' },
            { text: 'Changelog', link: '/guide/changelog' },
          ]
        }
      ],
      '/architecture/': [
        {
          text: 'Architecture',
          items: [
            { text: 'Overview', link: '/architecture/overview' },
            { text: 'Core Decisions', link: '/architecture/decisions' },
            { text: 'Tech Stack', link: '/architecture/stack' },
            { text: 'Multi-Tenancy', link: '/architecture/multi-tenancy' },
            { text: 'Content Types', link: '/architecture/content-types' },
            { text: 'AI Architecture', link: '/architecture/ai' },
            { text: 'Deployment', link: '/architecture/deployment' },
            { text: 'Business Model', link: '/architecture/business-model' },
            { text: 'Open Questions', link: '/architecture/open-questions' },
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/your-org/ekklesia-cms' }
    ],

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Ekklesia CMS — Built for the African Church'
    },

    editLink: {
      pattern: 'https://github.com/your-org/ekklesia-cms/edit/main/docs/:path',
      text: 'Edit this page on GitHub'
    },

    search: {
      provider: 'local'
    }
  }
})
