<template>
    <router-link is="sui-card" :to="{ name: 'apps.view', params: { id: site.id } }">
        <sui-card-content>
            <sui-card-header>{{ site.name }}</sui-card-header>
            <sui-card-meta>{{ site.primary_domain }}</sui-card-meta>
        </sui-card-content>

        <sui-card-content extra>
            <router-link :to="{ name: 'files', params: { path: site.document_root } }"
                slot="right" v-if="site.document_root != null">
                <sui-icon name="open folder" /> Browse Files
            </router-link>
        </sui-card-content>
    </router-link>
</template>

<script>
export default {
    props: {
        site: Object,
    },
}
</script>
