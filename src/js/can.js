const can = function (ability) {
  let abilities = this.$page.props.authUser.abilities || [];
  let appName = this.$page.props.appName || '';

  let godMode = abilities.includes('god_mode:enabled');
  let projectPermission = abilities.includes(appName.toLowerCase() + ':' + ability);
  let globalPermission = abilities.includes('global:' + ability);

  return godMode || projectPermission || globalPermission;
};

export { can };
