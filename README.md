# EnderChestSlots
A PocketMine-MP plugin that allows you to restrict slots of EnderChests

## Usage

### Getting the config.yml of the plugin

```YAML
---
# 27 is the maximum of slots
# use "default" has permission to set the default number of slots

restricted: §r§cRestricted

permissions: 
  - 
    permission: default
    slots: 5
    
  - 
    permission: enderchest.vip
    slots: 8
    
  - 
    permission: enderchest.staff
    slots: 27
```
