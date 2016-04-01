# Specify the port (and possibly IP) that the docker container should
# bind to
NETWORK_BIND_1="-p 80:80"

# If you have a registry that you wish to push to, then uncomment the REGISTRY line below and
# give it a value.
# If you want to use the public docker hub, then this would just be your username.
# If you have a private registry then it would need to be something like:
# REGISTRY="registry.my-domain.org:5000"
#REGISTRY=""