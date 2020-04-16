Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'sofre-api',
      path: '/sofre-api',
      component: require('./components/Tool'),
    },
  ])
})
