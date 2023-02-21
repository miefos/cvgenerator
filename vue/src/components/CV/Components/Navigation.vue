<template>
  <ul class="surface-card p-2 m-0 list-none flex overflow-x-auto select-none">
    <template v-for="(tab, i) in tabs" :key="tab.name">
      <li :class="i === tabs.length - 1 ? 'pl-3' : (i === 0 ? 'pr-3' : 'px-3')">
        <router-link :to="tab.path" >
          <span class="cursor-pointer px-4 py-3 flex align-items-center hover:surface-100 border-round transition-colors transition-duration-150 p-ripple"
             :class="{'bg-primary hover:bg-primary': $route.path === tab.path, 'text-700': ''}">
            <i :class="'pi ' + tab.icon + ' mr-2'"></i>
            <span class="font-medium">{{ tab.name }}</span>
          </span>
        </router-link>
      </li>
      <li v-if="i !== tabs.length - 1" class="flex align-items-center">
        <div style="width:1px; height: 50%" class="border-right-1 surface-border"></div>
      </li>
    </template>
  </ul>
</template>

<script>
export default {
  name: "Navigation",
  props: {
    tabs: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>

</style>