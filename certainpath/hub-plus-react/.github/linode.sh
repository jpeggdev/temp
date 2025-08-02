#!/bin/bash

GH_ACTION_LABEL="gh-action-ip"

add_rule() {
  LINODE_FW_ID=$LINODE_FW_ID
  GH_RUNNER_IP=$GH_RUNNER_IP

  CURRENT_RULES=$(linode-cli firewalls view $LINODE_FW_ID --json | jq '.[0].rules.inbound')

  NEW_RULE='{"action":"ACCEPT", "protocol": "TCP", "ports": "22", "addresses": {"ipv4": ["'$GH_RUNNER_IP'"]}, "label": "'$GH_ACTION_LABEL'"}'

  UPDATED_RULES=$(echo $CURRENT_RULES | jq ". + [$NEW_RULE]")

  linode-cli firewalls rules-update $LINODE_FW_ID --inbound "$UPDATED_RULES" --outbound '[]'
}

remove_rule() {
  LINODE_FW_ID=$LINODE_FW_ID

  CURRENT_RULES=$(linode-cli firewalls view $LINODE_FW_ID --json | jq '.[0].rules.inbound')

  UPDATED_RULES=$(echo $CURRENT_RULES | jq 'map(select(.label != "'$GH_ACTION_LABEL'"))')

  linode-cli firewalls rules-update $LINODE_FW_ID --inbound "$UPDATED_RULES" --outbound '[]'  # Keeps the outbound as empty (modify if needed)
}

while getopts "ad" option; do
  case $option in
    a)
      add_rule
      ;;
    d)
      remove_rule
      ;;
    *)
      echo "Invalid option: Use -a to add IP or -d to remove IP"
      exit 1
      ;;
  esac
done

if [ $OPTIND -eq 1 ]; then
  echo "No option passed. Use -a to add IP or -d to remove IP."
  exit 1
fi
